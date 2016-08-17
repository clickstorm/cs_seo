<?php
return [
	'ctrl' => [
		'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'hideTable' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

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
		'showRecordFieldList' => 'hidden, sys_language_uid, l10n_parent, l10n_diffsource, title, description, title_only, 
								  canonical, no_index, og_title, og_description, og_image, tw_title, tw_description, tw_image, tw_creator',
	],
	'types' => [
		'1' => ['showitem' => '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo, 
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;preview,keyword,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_index;index,
							    --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;facebook,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;twitter,
								--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, sys_language_uid, l10n_parent, l10n_diffsource, hidden;;1, starttime, endtime'],
	],
	'palettes' => [
		'preview' => ['showitem' => 'title,title_only,--linebreak--,
                                    description'],

		'index' => ['showitem' => 'canonical,no_index'],

		'facebook' => ['showitem' => 'og_title, --linebreak--,
									    og_description, --linebreak--,
									    og_image'],

		'twitter' => ['showitem' => 'tw_title, --linebreak--,
								    tw_description, --linebreak--,
								    tw_image, --linebreak--,
								    tw_creator']
	],
	'columns' => [

		'sys_language_uid' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
				],
			],
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0],
				],
				'foreign_table' => 'tx_csseo_domain_model_meta',
				'foreign_table_where' => 'AND tx_csseo_domain_model_meta.pid=###CURRENT_PID### AND tx_csseo_domain_model_meta.sys_language_uid IN (-1,0)',
			],
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
			],
		],
		't3ver_label' => [
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],

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

		'title' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title',
			'exclude' => 1,
			'config' => [
				'type' => 'input',
				'max' => $extConf['maxTitle'],
				'eval' => 'trim',
				'wizards' => [
					'_POSITION' => 'bottom',
					'previewWizard' => [
						'type' => 'userFunc',
						'userFunc' => 'Clickstorm\\CsSeo\\UserFunc\\PreviewWizard->render'
					]
				]
			]
		],
		'description' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta.description',
			'exclude' => 1,
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 5,
				'eval' => 'trim',
				'max' => '200',
			]
		],
		'title_only' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title_only',
			'exclude' => 1,
			'config' => [
				'type' => 'check',
			]
		],
		'keyword' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_keyword',
			'exclude' => 1,
			'config' => [
				'type' => 'input',
				'max' => '256',
				'eval' => 'trim',
			]
		],
		'canonical' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_canonical',
			'exclude' => 1,
			'displayCond' => 'FIELD:tx_csseo_no_index:REQ:FALSE',
			'config' => [
				'type' => 'input',
				'max' => '256',
				'eval' => 'trim',
				'wizards' => [
					'link' => [
						'type' => 'popup',
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
						'icon' => 'link_popup.gif',
						'module' => [
							'name' => 'wizard_element_browser',
							'urlParameters' => [
								'mode' => 'wizard',
							]
						],
						'params' => [
							'blindLinkOptions' => 'file, folder, mail, spec',
							'blindLinkFields' => 'class, params, target, title',
						],
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					]
				],
				'softref' => 'typolink'
			]
		],
		'no_index' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_index',
			'exclude' => 1,
			'config' => [
				'type' => 'check',
			]
		],
		'og_title' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_title',
			'exclude' => 1,
			'config' => [
				'type' => 'input',
				'max' => '95',
				'eval' => 'trim',
			]
		],
		'og_description' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_description',
			'exclude' => 1,
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 5,
				'eval' => 'trim',
				'max' => '300',
			]
		],
		'og_image' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_image',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'tx_csseo_og_image',
				[
					'appearance' => [
						'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
					],
					'foreign_types' => [
						'0' => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
							'showitem' => '
                        --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette'
						]
					],
					'maxitems' => 1
				],
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
			),
		],
		'tw_title' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_title',
			'exclude' => 1,
			'config' => [
				'type' => 'input',
				'max' => '70',
				'eval' => 'trim',
			]
		],
		'tw_description' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_description',
			'exclude' => 1,
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 5,
				'eval' => 'trim',
				'max' => '200',
			]
		],
		'tw_image' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_image',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'tx_csseo_tw_image',
				[
					'appearance' => [
						'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
					],
					'foreign_types' => [
						'0' => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
							'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
						]
					],
					'maxitems' => 1
				],
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
			),
		],
		'tw_creator' => [
			'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_creator',
			'exclude' => 1,
			'config' => [
				'type' => 'input',
				'max' => '40',
				'eval' => 'trim',
			]
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