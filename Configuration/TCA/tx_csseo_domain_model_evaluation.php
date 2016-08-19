<?php
return [
	'ctrl' => [
		'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation',
		'label' => 'results',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'hideTable' => TRUE,

		'delete' => 'deleted',

		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => 'title,description,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cs_seo') . 'Resources/Public/Icons/mod.png'
	],
	'interface' => [
		'showRecordFieldList' => 'restults',
	],
	'types' => [
		'1' => ['showitem' => 'results'],
	],
	'palettes' => [
	],
	'columns' => [

		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				],
			],
		],
		'endtime' => [
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				],
			],
		],

		'results' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation.results',
			'config' => [
				'type' => 'passthrough',
			],
		],

		'uid_foreign' => [
			'config' => [
				'type' => 'passthrough'
			],
		],
		'tablenames' => [
			'config' => [
				'type' => 'passthrough'
			],
		]
	],
];