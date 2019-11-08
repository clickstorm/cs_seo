<?php
return [
	'ctrl' => [
		'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation',
		'label' => 'results',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'hideTable' => TRUE,

		'delete' => 'deleted',

		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => 'title,description,',
		'iconfile' => 'EXT:cs_seo/Resources/Public/Icons/mod.png'
	],
	'interface' => [
		'showRecordFieldList' => 'results',
	],
	'types' => [
		'1' => ['showitem' => 'results'],
	],
	'palettes' => [
	],
	'columns' => [
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
			],
		],
		'endtime' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
			],
		],
		'results' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation.results',
			'config' => [
				'type' => 'passthrough',
			],
		],
        'url' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation.url',
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
