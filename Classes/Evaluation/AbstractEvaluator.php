<?php

namespace Clickstorm\CsSeo\Evaluation;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class AbstractEvaluator
 */
abstract class AbstractEvaluator implements EvaluationInterface
{
    const STATE_GREEN = 2;
    const STATE_YELLOW = 1;
    const STATE_RED = 0;

    /**
     * @var \DOMDocument
     */
    protected $domDocument;

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $bodyContent = '';

    /**
     * TSFEUtility constructor.
     *
     * @param \DOMDocument $domDocument
     * @param string $keyword
     */
    public function __construct($domDocument, $keyword = '')
    {
        $this->domDocument = $domDocument;
        $this->setKeyword($keyword);
    }

    /**
     * @return \DOMDocument
     */
    public function getDomDocument()
    {
        return $this->domDocument;
    }

    /**
     * @param \DOMDocument $domDocument
     */
    public function setDomDocument($domDocument)
    {
        $this->domDocument = $domDocument;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = strtolower($keyword);
    }

    public function validate()
    {
        return [];
    }

    /**
     * @param string $tagName
     *
     * @return string
     */
    protected function getSingleDomElementContentByTagName($tagName)
    {
        $elements = $this->domDocument->getElementsByTagName($tagName);
        if ($elements->item(0)) {
            return $elements->item(0)->textContent;
        }
        return '';
    }

    /**
     * @param $metaName
     *
     * @return int
     */
    protected function getNumberOfMetaTags($metaName)
    {
        $counter = 0;
        $metaTags = $this->domDocument->getElementsByTagName('meta');

        /** @var \DOMElement $metaTag */
        foreach ($metaTags as $metaTag) {
            if ($metaTag->getAttribute('name') == $metaName) {
                $counter++;
            }
        }

        return $counter;
    }

    protected function getMetaTagContent($metaName)
    {
        $content = '';
        $metaTags = $this->domDocument->getElementsByTagName('meta');

        /** @var \DOMElement $metaTag */
        foreach ($metaTags as $metaTag) {
            if ($metaTag->getAttribute('name') == $metaName) {
                $content = $metaTag->getAttribute('content');
                break;
            }
        }

        return $content;
    }
}
