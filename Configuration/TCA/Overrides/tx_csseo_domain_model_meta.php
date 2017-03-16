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

    $tables = \Clickstorm\CsSeo\Utility\ConfigurationUtility::getTablesToExtend();
	if($tables) {
		foreach ($tables as $table) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table,$tempColumns);
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
                $table,
                '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,tx_csseo'
            );
		}
	}
}