<?php

namespace Clickstorm\CsSeo\Evaluation;

abstract class AbstractEvaluator implements EvaluationInterface
{
    const STATE_GREEN = 2;
    const STATE_YELLOW = 1;
    const STATE_RED = 0;

    protected ?\DOMDocument $domDocument = null;

    protected string $keyword = '';

    protected string $bodyContent = '';

    public function __construct(\DOMDocument $domDocument, string $keyword = '')
    {
        $this->domDocument = $domDocument;
        $this->setKeyword($keyword);
    }

    public function getDomDocument(): \DOMDocument
    {
        return $this->domDocument;
    }

    public function setDomDocument(\DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword)
    {
        $this->keyword = strtolower($keyword);
    }

    public function validate(): array
    {
        return [];
    }

    protected function getSingleDomElementContentByTagName(string $tagName): string
    {
        $elements = $this->domDocument->getElementsByTagName($tagName);
        if ($elements->item(0) !== null) {
            return $elements->item(0)->textContent;
        }
        return '';
    }

    protected function getNumberOfMetaTags(string $metaName): int
    {
        $counter = 0;
        $metaTags = $this->domDocument->getElementsByTagName('meta');

        /** @var \DOMElement $metaTag */
        foreach ($metaTags as $metaTag) {
            if ($metaTag->getAttribute('name') === $metaName) {
                $counter++;
            }
        }

        return $counter;
    }

    protected function getMetaTagContent(string $metaName): string
    {
        $content = '';
        $metaTags = $this->domDocument->getElementsByTagName('meta');

        /** @var \DOMElement $metaTag */
        foreach ($metaTags as $metaTag) {
            if ($metaTag->getAttribute('name') === $metaName) {
                $content = $metaTag->getAttribute('content');
                break;
            }
        }

        return $content;
    }
}
