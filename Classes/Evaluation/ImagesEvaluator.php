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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImagesEvaluator
 *
 * @package Clickstorm\CsSeo\Evaluation
 */
class ImagesEvaluator extends AbstractEvaluator
{

    public function evaluate()
    {
        $state = self::STATE_RED;
        $imagesWithoutAlt = [];
        $altCount = 0;
        $baseUrl = '';

        $images = $this->domDocument->getElementsByTagName('img');
        $count = $images->length;
        $baseTags = $this->domDocument->getElementsByTagName('base');
        foreach($baseTags as $baseTag) {
        	$baseUrl = $baseTag->getAttribute('href');
        }

        /** @var \DOMElement $element */
        foreach ($images as $element) {
            $alt = $element->getAttribute('alt');
            if (empty($alt)) {
	            $url = $element->getAttribute('src');
	            if (!GeneralUtility::isValidUrl($url)) {
		            if($baseUrl) {
		            	$url = $baseUrl . $url;
		            }
	            }
	            $imagesWithoutAlt[] = $url;
            } else {
                $altCount++;
            }
        }

        if ($count == $altCount) {
            $state = self::STATE_GREEN;
        } else {
            if ($altCount > 0) {
                $state = self::STATE_YELLOW;
            }
        }

        return [
            'count' => $count,
            'altCount' => $altCount,
            'countWithoutAlt' => $count - $altCount,
            'state' => $state,
            'images' => $imagesWithoutAlt
        ];
    }
}
