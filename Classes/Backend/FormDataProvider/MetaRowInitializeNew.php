<?php

namespace Clickstorm\CsSeo\Backend\FormDataProvider;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Form\FormEngine;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Fill the news records with default values
 */
class MetaRowInitializeNew implements FormDataProviderInterface
{

    /**
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
//        // check if table uses CsSeo
//        if(!$this->isAllowedTable($result['tableName'], $result['pageTsConfig'])) return $result;
//
//        // Add TCA Field
//        $tempColumns = [
//            'tx_csseo' => [
//                'exclude' => 0,
//                'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
//                'config' => [
//                    'type' => 'inline',
//                    'foreign_table' => 'tx_csseo_domain_model_meta',
//                    'foreign_field' => 'uid_foreign',
//                    'foreign_table_field' => 'tablenames',
//                    'maxitems' => 1,
//                    'appearance' => [
//                        'showPossibleLocalizationRecords' => true,
//                        'showRemovedLocalizationRecords' => true,
//                        'showSynchronizationLink' => true,
//                        'showAllLocalizationLink' => true,
//	                    'levelLinksPosition' => 'top',
//
//                        'enabledControls' => [
//                            'info' => true,
//                            'new' => true,
//                            'dragdrop' => false,
//                            'sort' => false,
//                            'hide' => true,
//                            'delete' => true,
//                            'localize' => true,
//                        ],
//                    ],
//                    'behaviour' => [
//                        'localizationMode' => 'select',
//                        'localizeChildrenAtParentLocalization' => true,
//                    ],
//                ],
//            ]
//        ];
//
//        ExtensionManagementUtility::addTCAcolumns($result['tableName'],$tempColumns,1);
//        ExtensionManagementUtility::addToAllTCAtypes($result['tableName'],'tx_csseo');
//
//        $result['processedTca']['columns'] = array_merge($result['processedTca']['columns'], $tempColumns);
//        $result['columnsToProcess'][] = 'tx_csseo';
//        $result['processedTca']['types'][0]['showitem'] .= ',tx_csseo';
//        $result['processedTca']['interface']['showRecordFieldList'] .= ',tx_csseo';
//
//        // Add default values
//        if(intval($result['databaseRow']['uid']) > 0) {
//            $result['databaseRow']['tx_csseo'] = $this->getRecordMeta($result['tableName'], $result['databaseRow']['uid']);
//        }
//
        return $result;
    }

    /**
     * check if current table uses open graph
     *
     * @param $table
     * @param $pageUid
     * @return bool
     */
    protected function isAllowedTable($table, $pageTS) {
        if($pageTS['tx_csseo.']) {
            return in_array($table, $pageTS['tx_csseo.']);
        }
    }

    /**
     * return the uid of existing Open Graph options
     *
     * @param $table
     * @param $uid
     * @return bool
     */
    private function getRecordMeta($table, $uid) {
        $meta = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid',
            'tx_csseo_domain_model_meta',
            'tablenames = "' . $table . '" AND uid_foreign = ' . $uid
        );

        return count($meta) === 0 ? false : $meta[0]['uid'];
    }
}