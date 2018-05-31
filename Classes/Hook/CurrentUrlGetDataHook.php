<?php

namespace Clickstorm\CsSeo\Hook;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectGetDataHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018 Marco Pfeiffer, https://www.marco.zone/
 *  (c) 2018 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
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
class CurrentUrlGetDataHook implements ContentObjectGetDataHookInterface
{
    /**
     * retrieve all parameters for cHash calculation and return them as string
     * used in lib.currentUrl
     *
     * @param string $getDataString
     * @param array $fields
     * @param string $sectionValue
     * @param string $returnValue
     * @param ContentObjectRenderer $parentObject
     * @return string
     */
    public function getDataExtension(
        $getDataString,
        array $fields,
        $sectionValue,
        $returnValue,
        ContentObjectRenderer &$parentObject
    ) {
        if ($getDataString !== 'tx_csseo_url_parameters') {
            return $returnValue;
        }

        $cHash_array = $GLOBALS['TSFE']->cHash_array;
        unset($cHash_array['encryptionKey']);

        return GeneralUtility::implodeArrayForUrl('', $cHash_array);
    }
}