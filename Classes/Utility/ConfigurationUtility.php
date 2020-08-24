<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Site\Entity\Site;
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
        $config = self::getYamlConfig();

        return isset($config['records']) && is_array($config['records']) ?
            $config['records'] : [];
    }

    /**
     * return the settings to extend records
     *
     * @return array
     */
    public static function getYamlConfig()
    {
        $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $config = [];

        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['yamlConfigFile']) && is_string($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['yamlConfigFile'])) {
            $newConfig = $fileLoader->load($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['yamlConfigFile']);
            $config = $newConfig;
        }

        return $config;
    }

    /**
     * return the settings for a table
     *
     * @return array
     */
    public static function getTableSettings($tableName)
    {
        $config = self::getYamlConfig();

        return isset($config['records'][$tableName]) && is_array($config['records'][$tableName])
            ? $config['records'][$tableName] : [];
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
     * @return bool
     */
    public static function useAdditionalCanonicalizedUrlParametersOnly()
    {
        $extConf = self::getEmConfiguration();

        return (bool)$extConf['useAdditionalCanonicalizedUrlParametersOnly'];
    }

    /**
     * returns the x-default from site config
     *
     * @return int
     */
    public static function getXdefault()
    {
        $xDefault = 0;

        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');

        if (isset($site->getConfiguration()['txCsseoXdefault'])) {
            $xDefault = (int)$site->getAttribute('txCsseoXdefault');
        }

        return $xDefault;
    }
}
