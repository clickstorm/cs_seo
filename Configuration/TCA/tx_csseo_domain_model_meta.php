<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Resource\File;

$extConf = ConfigurationUtility::getEmConfiguration();

$openGraphCropConfiguration = [
    'config' => [
        'cropVariants' => [
            'default' => [
                'disabled' => true,
            ],
            'social' => [
                'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.crop_variant.social',
                'coverAreas' => [],
                'cropArea' => [
                    'x' => '0.0',
                    'y' => '0.0',
                    'width' => '1.0',
                    'height' => '1.0',
                ],
                'allowedAspectRatios' => [
                    '1.91:1' => [
                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.191_1',
                        'value' => 1200 / 630,
                    ],
                    'NaN' => [
                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.free',
                        'value' => 0.0,
                    ],
                ],
                'selectedRatio' => '1.91:1',
            ],
        ],
    ],
];

return [
    'ctrl' => [
        'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,

        'hideTable' => true,

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',

        'security' => [
            'ignorePageTypeRestriction' => true
        ],

        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,description,',
        'iconfile' => 'EXT:cs_seo/Resources/Public/Icons/mod.png',
    ],
    'types' => [
        '1' => [
            'showitem' => '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;preview,keyword,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_index;index,
							    --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;facebook,
							    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;twitter,
							    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.metadata,
							    json_ld,
							    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
							    --palette--;;language,
							    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
							    hidden,--palette--;;access',
        ],
    ],
    'palettes' => [
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                            endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ],

        'preview' => [
            'showitem' => 'title_only,--linebreak--,title,--linebreak--,description',
        ],

        'index' => ['showitem' => 'canonical,no_index,no_follow'],

        'facebook' => [
            'showitem' => 'og_title, --linebreak--,
									    og_description, --linebreak--,
									    og_image',
        ],

        'language' => [
            'showitem' => 'sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,l18n_parent',
        ],

        'twitter' => [
            'showitem' => 'tw_title, --linebreak--,
								    tw_description, --linebreak--,
								    tw_image, --linebreak--,
								    tw_creator, tw_site',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tx_csseo_domain_model_meta',
                'foreign_table_where' => 'AND {#tx_csseo_domain_model_meta}.{#pid}=###CURRENT_PID### AND {#tx_csseo_domain_model_meta}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'title' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:seo_title.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'renderType' => 'snippetPreview',
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta.description',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:description.description',
            'exclude' => 1,
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
                'max' => $extConf['maxDescription'] ?? '',
            ],
        ],
        'title_only' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title_only',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:tx_csseo_title_only.description',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'keyword' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_keyword',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:tx_csseo_keyword.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => '256',
                'eval' => 'trim',
            ],
        ],
        'canonical' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_canonical',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:canonical_link.description',
            'exclude' => 1,
            'displayCond' => 'FIELD:tx_csseo_no_index:REQ:FALSE',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['page', 'url', 'record'],
                'size' => 50,
                'appearance' => [
                    'browserTitle' => 'LLL:EXT:seo/Resources/Private/Language/locallang_tca.xlf:pages.canonical_link',
                    'allowedOptions' => ['params', 'rel'],
                ],
            ],
        ],
        'no_index' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_index',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:no_index.description',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'no_follow' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_follow',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:no_follow.description',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'og_title' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_title',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:og_title.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => '95',
                'eval' => 'trim',
            ],
        ],
        'og_description' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_description',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:og_description.description',
            'exclude' => 1,
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
                'max' => '300',
            ],
        ],
        'og_image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_image',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:og_image.description',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                    ],
                    'columns' => [
                        'crop' => $openGraphCropConfiguration,
                    ],
                ],
            ]
        ],
        'tw_title' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_title',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:twitter_title.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => '70',
                'eval' => 'trim',
            ],
        ],
        'tw_description' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_description',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:twitter_description.description',
            'exclude' => 1,
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
                'max' => '200',
            ],
        ],
        'tw_image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_image',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:twitter_image.description',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                    ],
                    'columns' => [
                        'crop' => $openGraphCropConfiguration,
                    ],
                ],
            ]
        ],
        'tw_creator' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_creator',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:tx_csseo_tw_creator.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => '40',
                'eval' => 'trim',
            ],
        ],
        'tw_site' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_site',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:tx_csseo_tw_site.description',
            'exclude' => 1,
            'config' => [
                'type' => 'input',
                'max' => '40',
                'eval' => 'trim',
            ],
        ],
        'json_ld' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_json_ld',
            'description' => 'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:tx_csseo_json_ld.description',
            'exclude' => 1,
            'config' => [
                'type' => 'text',
                'renderType' => 'txCsseoJsonLd',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
                'eval' => 'trim,Clickstorm\\CsSeo\\Evaluation\\TCA\\JsonLdEvaluator',
            ],
        ],
        'uid_foreign' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tablenames' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
