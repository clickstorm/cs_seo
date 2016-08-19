<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'SEO');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_csseo_domain_model_meta',
    'EXT:cs_csseo/Resources/Private/Language/locallang_csh_tx_csseo_domain_model_meta.xlf');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_csseo_domain_model_meta');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages_language_overlay',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');

/**
 * Include Backend Module
 * @todo remove condition for TYPO3 6.2 in upcoming major version
 */
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Clickstorm.' . $_EXTKEY,
        'web',
        'mod1',
        '',
        array(
            'Module' => 'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageResults, pageEvaluation'
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/mod.' .
                (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0') ? 'svg' : 'png'),
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
        )
    );
}

// Add TCA Field
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

	$rootLine = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine(1, '', true);
	$pageTS = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(1, $rootLine);

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

// register Ajax Handler
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
    'CsSeo::update',
    'Clickstorm\\CsSeo\\Controller\\ModuleController->update'
);

// register Ajax Handler
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
	'CsSeo::evaluate',
	'Clickstorm\\CsSeo\\Command\\EvaluationCommandController->ajaxUpdate'
);