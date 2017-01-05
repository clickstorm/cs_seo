<?php
defined('TYPO3_MODE') || die('Access denied.');

/*
 * Add columns to sys domain record
 */
$temporaryTcaSysDomainColumns = [
	'tx_csseo_robots_txt' => [
		'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:sys_domain.tx_csseo_robots_txt',
		'exclude' => 1,
		'config' => [
			'type' => 'text',
			'cols' => 40,
			'rows' => 20,
			'eval' => 'Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsDisallowAllEvaluator,Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsExistsEvaluator'
		]
	],

];


// add new fields
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'sys_domain',
	$temporaryTcaSysDomainColumns
);

// add new fields to types
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_domain',
	'--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:sys_domain.tab.robots_txt,tx_csseo_robots_txt'
);