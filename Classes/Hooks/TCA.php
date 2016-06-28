<?php
namespace Clickstorm\CsSeo\Hooks;

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

use TYPO3\CMS\Backend\Form\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA Hook
 *
 * @package Clicsktrom\CsSeo
 */
class TCA {

	/**
	 * Hook for showing fields in the TCA
	 * @param $table
	 * @param $row
	 * @param FormEngine $oParent
	 */
	public function getMainFields_preProcess($table, &$row, FormEngine $oParent) {
		$pidField = $table == 'pages' ? 'uid' : 'pid';
		// check if table uses CsSeo
		if(!$this->isAllowedTable($table, $row[$pidField])) return;

		// Add TCA Field
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
					],
				],
			]
		];
		ExtensionManagementUtility::addTCAcolumns($table,$tempColumns,1);
		ExtensionManagementUtility::addToAllTCAtypes($table,'--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta,tx_csseo');
		
		// Add default values
		if(intval($row['uid']) > 0) {
			$row['tx_csseo'] = $this->getRecordMeta($table,$row['uid']);
		}
	}

	/**
	 * store the open graph tablenames an uid_foreign
	 *
	 * @param $status
	 * @param string $table
	 * @param int $uid
	 * @param array $fieldArray
	 * @param DataHandler $dh
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $uid, &$fieldArray, DataHandler $dh) {
		if(!$this->isAllowedTable($table, GeneralUtility::_POST('popViewId'))) return;
		$i_realRecordUid = (is_numeric($uid)) ? $uid : $dh->substNEWwithIDs[$uid];

		if(isset($dh->datamap['tx_csseo_domain_model_meta'])) {
			foreach ($dh->datamap['tx_csseo_domain_model_meta'] as &$meta) {
				$meta['tablenames'] = $table;
				$meta['uid_foreign'] = $i_realRecordUid;

			}
		}
	}

	/**
	 * check if current table uses open graph
	 *
	 * @param $table
	 * @param $pageUid
	 * @return bool
	 */
	protected function isAllowedTable($table, $pageUid) {
		$setup = BackendUtility::getPagesTSconfig($pageUid);
		if($setup['tx_csseo.']) {
			return in_array($table, $setup['tx_csseo.']);
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
			'tx_csopengraph_domain_model_opengraph',
			'tablenames = "' . $table . '" AND uid_foreign = ' . $uid
		);

		return count($meta) === 0 ? false : $meta[0]['uid'];
	}
}