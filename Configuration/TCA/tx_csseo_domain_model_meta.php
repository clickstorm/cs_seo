<?php

$extConf = \Clickstorm\CsSeo\Utility\ConfigurationUtility::getEmConfiguration();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'versioningWS' => true,

        'hideTable' => true,

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
        'iconfile' => 'EXT:cs_seo/Resources/Public/Icons/mod.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, sys_language_uid, l10n_parent, l10n_diffsource, title, description, title_only, 
								  canonical, no_index, no_follow, og_title, og_description, og_image, tw_title, tw_description, 
								  tw_image, tw_creator, tw_site',
    ],
    'types' => [
        '1' => [
            'showitem' => '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo, 
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;preview,keyword,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_index;index,
							    --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;facebook,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;twitter,
							    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
							    --palette--;;language,
							    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
							    hidden,--palette--;;access'
        ],
    ],
    'palettes' => [
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel, 
                            endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel'
        ],

        'preview' => [
            'showitem' => 'title,title_only,--linebreak--,
                                    description'
        ],

        'index' => ['showitem' => 'canonical,no_index,no_follow'],

        'facebook' => [
            'showitem' => 'og_title, --linebreak--,
									    og_description, --linebreak--,
									    og_image'
        ],

        'language' => [
            'showitem' => 'sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,l18n_parent'
        ],

        'twitter' => [
            'showitem' => 'tw_title, --linebreak--,
								    tw_description, --linebreak--,
								    tw_image, --linebreak--,
								    tw_creator, tw_site'
        ]
    ],
    'columns' => [

        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'default' => 0,
                'items' => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
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

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle'
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

        'title' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'renderType' => 'snippetPreview',
                'max' => $extConf['maxTitle'],
                'eval' => 'trim'
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
                'max' => $extConf['maxDescription'],
            ]
        ],
        'title_only' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title_only',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle'
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
                'renderType' => 'inputLink',
                'max' => '256',
                'eval' => 'trim',
                'softref' => 'typolink'
            ]
        ],
        'no_index' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_index',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle'
            ]
        ],
        'no_follow' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_follow',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle'
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
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ],
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
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ],
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
        'tw_site' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_site',
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