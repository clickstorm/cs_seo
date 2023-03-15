<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Get Extension Configuration
 *
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{
    /**
     * return the table names to extend
     */
    public static function getTablesToExtend(): array
    {
        $config = self::getYamlConfig();

        return isset($config['records']) && is_array($config['records']) ?
            $config['records'] : [];
    }

    /**
     * return the settings to extend records
     */
    public static function getYamlConfig(): array
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
     */
    public static function getTableSettings($tableName): array
    {
        $config = self::getYamlConfig();

        return isset($config['records'][$tableName]) && is_array($config['records'][$tableName])
            ? $config['records'][$tableName] : [];
    }

    /**
     * return the allowed doktypes of pages for evaluation
     */
    public static function getEvaluationDoktypes(): array
    {
        $allowedDoktypes = [1];
        $extConf = self::getEmConfiguration();
        if (!empty($extConf['evaluationDoktypes'])) {
            $allowedDoktypes = GeneralUtility::trimExplode(',', $extConf['evaluationDoktypes']);
        }

        return $allowedDoktypes;
    }

    /**
     * Get the configuration from the extension manager
     */
    public static function getEmConfiguration(): array
    {
        $conf = $confArray = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('cs_seo');

        return is_array($conf) ? $conf : [];
    }

    /**
     * returns the x-default from site config
     */
    public static function getXdefault(): int
    {
        $xDefault = 0;

        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');

        if (($site instanceof Site) && isset($site->getConfiguration()['txCsseoXdefault'])) {
            $xDefault = (int)$site->getAttribute('txCsseoXdefault');
        }

        return $xDefault;
    }
}
