<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Get Extension Configuration
 *
 * Class ConfigurationUtility
 *
 */
class ConfigurationUtility
{

    /**
     * return the table names to extend
     *
     * @return array
     */
    public static function getTablesToExtend()
    {
        $pageTSconfig = self::getPageTSconfig();
        $tables = [];
        if ($pageTSconfig) {
            foreach ($pageTSconfig as $table) {
                if (is_string($table)) {
                    $tables[] = $table;
                }
            }
        }

        return $tables;
    }

    /**
     * return the settings to extend records
     *
     * @return array
     */
    public static function getPageTSconfig()
    {
        $extConf = self::getEmConfiguration();
        $tsConfigPid = $extConf['tsConfigPid'] ?: 1;

        // get rootLine first to prevent caching from pageTSconfig
        $pageTSconfig = BackendUtility::getPagesTSconfig($tsConfigPid);

        return $pageTSconfig['tx_csseo.'] ?: [];
    }

    /**
     * Get the configuration from the extension manager
     *
     * @return array
     */
    public static function getEmConfiguration()
    {
        $conf = $confArray = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('cs_seo');

        return is_array($conf) ? $conf : [];
    }

    /**
     * return the settings for a table
     *
     * @return array
     */
    public static function getTableSettings($tableName)
    {
        $pageTSconfig = self::getPageTSconfig();
        $settings = [];
        if ($pageTSconfig) {
            foreach ($pageTSconfig as $tsConfigKey => $tsConfigRow) {
                if (is_string($tsConfigRow) && $tsConfigRow == $tableName) {
                    $settings = $pageTSconfig[$tsConfigKey . '.'];
                }
            }
        }

        return $settings;
    }

    /**
     * return the allowed doktypes of pages for evaluation
     *
     * @return array
     */
    public static function getEvaluationDoktypes()
    {
        $allowedDoktypes = [1];
        $extConf = self::getEmConfiguration();
        if ($extConf['evaluationDoktypes']) {
            $allowedDoktypes = GeneralUtility::trimExplode(',', $extConf['evaluationDoktypes']);
        }

        return $allowedDoktypes;
    }

    /**
     * @return bool
     */
    public static function useAdditionalCanonicalizedUrlParametersOnly() {
        $extConf = self::getEmConfiguration();
        return (bool)$extConf['useAdditionalCanonicalizedUrlParametersOnly'];
    }

    /**
     * @return int
     */
    public static function getXdefault() {
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        return (int)$site->getAttribute('txCsseoXdefault');
    }
}
