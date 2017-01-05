<?php
defined('TYPO3_MODE') || die('Access denied.');

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

	$confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

	$tsConfigPid = $confArray['tsConfigPid'] ?: 1;
	$rootLine = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($tsConfigPid, '', true);
	$pageTS = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($tsConfigPid, $rootLine);

	if($pageTS['tx_csseo.']) {
		foreach ($pageTS['tx_csseo.'] as $table) {
			if(is_string($table)) {
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table,$tempColumns,1);
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
					$table,
					'--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,tx_csseo'
				);
			}
		}
	}
}