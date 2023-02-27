<?php

namespace Clickstorm\CsSeo\Evaluation;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImagesEvaluator extends AbstractEvaluator
{
    public function evaluate(): array
    {
        $state = self::STATE_RED;
        $imagesWithoutAlt = [];
        $altCount = 0;
        $baseUrl = '';

        $images = $this->domDocument->getElementsByTagName('img');
        $count = $images->length;
        $baseTags = $this->domDocument->getElementsByTagName('base');
        foreach ($baseTags as $baseTag) {
            $baseUrl = $baseTag->getAttribute('href');
        }

        /** @var \DOMElement $element */
        foreach ($images as $element) {
            $alt = $element->getAttribute('alt');
            if (empty($alt)) {
                $url = $element->getAttribute('src');
                if (!GeneralUtility::isValidUrl($url) && $baseUrl) {
                    $url = $baseUrl . $url;
                }
                $imagesWithoutAlt[] = $url;
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
        ];
    }
}
