<?php

namespace Clickstorm\CsSeo\Evaluation;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

class ImagesEvaluator extends AbstractEvaluator
{
    private const MAX_IMAGES_RESULT_PAYLOAD_BYTES = 12 * 1024 * 1024; // 12 MiB
    private const MAX_EMBEDDED_IMAGE_URL_BYTES = 1048576; // 1 MiB

    public function evaluate(): array
    {
        $state = self::STATE_RED;
        $imagesWithoutAlt = [];
        $storedImagePayloadBytes = 0;
        $additionalImagesCount = 0;
        $omittedOversizedEmbeddedImagesCount = 0;
        $altCount = 0;
        $baseUrl = $this->extractBaseUrl();

        $images = $this->domDocument->getElementsByTagName('img');
        $count = $images->length;

        /** @var \DOMElement $element */
        foreach ($images as $element) {
            $alt = $element->getAttribute('alt');
            if ($alt === '') {
                // Treat decorative images as compliant.
                $role = strtolower(trim($element->getAttribute('role')));
                $ariaHidden = strtolower(trim($element->getAttribute('aria-hidden')));
                $isDecorative = in_array($role, ['presentation', 'none'], true) || $ariaHidden === 'true';
                if ($isDecorative) {
                    $altCount++;
                    continue;
                }

                $url = $this->extractImageUrl($element);
                $resolvedUrl = $this->resolveImageUrl($url, $baseUrl);

                if ($this->isOversizedEmbeddedUrl($resolvedUrl)) {
                    $additionalImagesCount++;
                    $omittedOversizedEmbeddedImagesCount++;
                    continue;
                }

                $estimatedBytes = $this->estimateStoredImageUrlBytes($resolvedUrl);
                if (($storedImagePayloadBytes + $estimatedBytes) > self::MAX_IMAGES_RESULT_PAYLOAD_BYTES) {
                    $additionalImagesCount++;
                    continue;
                }

                $imagesWithoutAlt[] = $resolvedUrl;
                $storedImagePayloadBytes += $estimatedBytes;
            } else {
                $altCount++;
            }
        }

        if ($count === $altCount) {
            $state = self::STATE_GREEN;
        } elseif ($altCount > 0) {
            $state = self::STATE_YELLOW;
        }

        return [
            'count' => $count,
            'altCount' => $altCount,
            'countWithoutAlt' => $count - $altCount,
            'state' => $state,
            'images' => $imagesWithoutAlt,
            'additionalImagesCount' => $additionalImagesCount,
            'omittedOversizedEmbeddedImagesCount' => $omittedOversizedEmbeddedImagesCount,
        ];
    }

    protected function extractImageUrl(\DOMElement $image): string
    {
        $srcsetValues = $this->extractPictureSourceSrcsets($image);
        $srcsetValues[] = $image->getAttribute('srcset');

        $embeddedFallbackUrl = '';

        foreach ($srcsetValues as $srcset) {
            foreach ($this->extractUrlsFromSrcset($srcset) as $candidateUrl) {
                if (!$this->isEmbeddedUrl($candidateUrl)) {
                    return $candidateUrl;
                }

                if ($embeddedFallbackUrl === '') {
                    $embeddedFallbackUrl = $candidateUrl;
                }
            }
        }

        $src = trim($image->getAttribute('src'));
        if ($src !== '' && !$this->isEmbeddedUrl($src)) {
            return $src;
        }

        return $embeddedFallbackUrl !== '' ? $embeddedFallbackUrl : $src;
    }

    /**
     * @return list<string>
     */
    private function extractPictureSourceSrcsets(\DOMElement $image): array
    {
        $picture = null;
        $sourceAncestors = [];
        $ancestor = $image->parentNode;

        while ($ancestor instanceof \DOMElement) {
            $nodeName = strtolower($ancestor->nodeName);

            if ($nodeName === 'source') {
                $sourceAncestors[] = $ancestor;
            }

            if ($nodeName === 'picture') {
                $picture = $ancestor;
                break;
            }

            $ancestor = $ancestor->parentNode;
        }

        if (!$picture instanceof \DOMElement) {
            return [];
        }

        // DOMDocument::loadHTML() uses an HTML4 parser that may incorrectly nest
        // img elements inside source elements. Source ancestors therefore represent
        // the source elements that precede the image in the original picture markup.
        if ($sourceAncestors !== []) {
            $srcsetValues = [];

            foreach (array_reverse($sourceAncestors) as $source) {
                $srcset = trim($source->getAttribute('srcset'));
                if ($srcset !== '') {
                    $srcsetValues[] = $srcset;
                }
            }

            return $srcsetValues;
        }

        $srcsetValues = [];

        foreach ($picture->childNodes as $child) {
            // Only source elements before the img element participate in picture selection.
            if ($child === $image) {
                break;
            }

            if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'source') {
                $srcset = trim($child->getAttribute('srcset'));
                if ($srcset !== '') {
                    $srcsetValues[] = $srcset;
                }
            }
        }

        return $srcsetValues;
    }

    /**
     * Extracts URL tokens from a srcset value without splitting commas inside data URLs.
     *
     * Descriptor validation is intentionally omitted because the evaluator only needs a
     * representative preview URL and does not emulate the browser's resource selection.
     *
     * @return list<string>
     */
    private function extractUrlsFromSrcset(string $srcset): array
    {
        $urls = [];
        $length = strlen($srcset);
        $position = 0;

        while ($position < $length) {
            while (
                $position < $length
                && ($srcset[$position] === ',' || $this->isAsciiWhitespace($srcset[$position]))
            ) {
                $position++;
            }

            if ($position >= $length) {
                break;
            }

            $urlStart = $position;
            while ($position < $length && !$this->isAsciiWhitespace($srcset[$position])) {
                $position++;
            }

            $urlToken = substr($srcset, $urlStart, $position - $urlStart);
            $hasTrailingSeparator = str_ends_with($urlToken, ',');
            $url = rtrim($urlToken, ',');

            if ($url !== '') {
                $urls[] = $url;
            }

            if ($hasTrailingSeparator) {
                continue;
            }

            $parenthesisDepth = 0;
            while ($position < $length) {
                $character = $srcset[$position];

                if ($character === '(') {
                    $parenthesisDepth++;
                } elseif ($character === ')' && $parenthesisDepth > 0) {
                    $parenthesisDepth--;
                } elseif ($character === ',' && $parenthesisDepth === 0) {
                    $position++;
                    break;
                }

                $position++;
            }
        }

        return $urls;
    }

    private function extractBaseUrl(): string
    {
        /** @var \DOMElement $baseTag */
        foreach ($this->domDocument->getElementsByTagName('base') as $baseTag) {
            if ($baseTag->hasAttribute('href')) {
                // HTML uses the first base element that defines an href attribute.
                return trim($baseTag->getAttribute('href'));
            }
        }

        return '';
    }

    private function resolveImageUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);
        if ($url === '' || $baseUrl === '' || $this->isEmbeddedUrl($url)) {
            return $url;
        }

        try {
            return (string)UriResolver::resolve(new Uri($baseUrl), new Uri($url));
        } catch (\InvalidArgumentException) {
            return $url;
        }
    }

    private function isOversizedEmbeddedUrl(string $url): bool
    {
        return $this->isEmbeddedUrl($url) && strlen($url) > self::MAX_EMBEDDED_IMAGE_URL_BYTES;
    }

    private function estimateStoredImageUrlBytes(string $url): int
    {
        // Rough upper bound including serialization overhead for the array key/value.
        return strlen($url) + 32;
    }

    private function isEmbeddedUrl(string $url): bool
    {
        return preg_match('#^(?:data|blob):#i', ltrim($url)) === 1;
    }

    private function isAsciiWhitespace(string $character): bool
    {
        return $character === ' '
            || $character === "\t"
            || $character === "\n"
            || $character === "\r"
            || $character === "\f";
    }
}
