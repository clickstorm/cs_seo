<?php

namespace Clickstorm\CsSeo\Hook;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Christian Forgács <christian@wunderbit.de>, wunderbit GmbH & Co. KG
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

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface ;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hook to extend the tca
 *
 * Class TCA
 *
 * @package Clickstorm\CsSeo\Hook
 */
class TableConfigurationPostProcessingHook implements TableConfigurationPostProcessingHookInterface {

    /**
     * Function which may process data created / registered by extTables
     * scripts (f.e. modifying TCA data of all extensions)
     *
     * @return void
     */
    public function processData() {
           
        // add new fields to pages
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'pages',
            '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo, 
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;tx_csseo_preview,tx_csseo_keyword,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_index;tx_csseo_index,
            --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;tx_csseo_facebook,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;tx_csseo_twitter',
            implode(',',\Clickstorm\CsSeo\Utility\ConfigurationUtility::getEvaluationDoktypes()),
            'after:lastUpdated'
        );
        
        // add new fields to pages_language_overlay
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'pages_language_overlay',
            '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;tx_csseo_preview,tx_csseo_keyword,
            --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;tx_csseo_facebook,
            --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;tx_csseo_twitter',
            implode(',',\Clickstorm\CsSeo\Utility\ConfigurationUtility::getEvaluationDoktypes()),
            'after:lastUpdated'
        );
        
        // Extend TCA of records like news etc.
        if(isset($GLOBALS['TYPO3_DB'])) {

            $tempColumns = [
                'tx_csseo' => [
                    'exclude' => 0,
                    'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
                    'config' => [
                        'type' => 'inline',
                        'foreign_table' => 'tx_csseo_domain_model_meta',
                        'foreign_field' => 'uid_foreign',
                        'foreign_table_field' => 'tablenames',
                        'maxitems' => 1,
                        'appearance' => [
                            'collapseAll' => false,
                            'showPossibleLocalizationRecords' => true,
                            'showRemovedLocalizationRecords' => true,
                            'showSynchronizationLink' => true,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'select',
                            'localizeChildrenAtParentLocalization' => TRUE,
                        ],
                    ],
                ]
            ];

            $tables = ConfigurationUtility::getTablesToExtend();
            if($tables) {
                foreach ($tables as $table) {
                    ExtensionManagementUtility::addTCAcolumns($table,$tempColumns);
                    ExtensionManagementUtility::addToAllTCAtypes(
                        $table,
                        '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,tx_csseo'
                    );
                }
            }
        }
    }

}