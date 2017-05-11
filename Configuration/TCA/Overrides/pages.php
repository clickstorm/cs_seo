<?php
defined('TYPO3_MODE') || die('Access denied.');

// get extension configurations
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cs_seo']);

// SEO Settings
$GLOBALS['TCA']['pages']['columns']['title']['config']['max'] = $extConf['maxTitle'];
$GLOBALS['TCA']['pages']['columns']['nav_title']['config']['max'] = $extConf['maxNavTitle'];
$GLOBALS['TCA']['pages']['columns']['description']['config']['max'] = $extConf['maxDescription'];

// Path segment auto fill
if($extConf['enablePathSegment'] && isset($GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment'])) {
    $GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment']['config']['eval'] .= ',required';
    $GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment']['config']['wizards'] = [
        '_POSITION' => 'bottom',
        'permalinkWizard' => [
            'type' => 'userFunc',
            'userFunc' => 'Clickstorm\\CsSeo\\UserFunc\\PermalinkWizard->render'
        ]
    ];
}

// define new fields
$tempColumns = [
    'tx_csseo_title' => [
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
    'tx_csseo_title_only' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title_only',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
        ]
    ],
	'tx_csseo_keyword' => [
		'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_keyword',
		'exclude' => 1,
		'config' => [
			'type' => 'input',
			'max' => 256,
			'size' => 48,
			'eval' => 'trim',
		],
	],
    'tx_csseo_canonical' => [
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
                    'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                    'icon' => 'actions-wizard-link',
                    'module' => [
                        'name' => 'wizard_link',
                    ],
                    'params' => [
                        'blindLinkOptions' => 'file, folder, mail, spec',
                        'blindLinkFields' => '',
                    ],
                    'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                ]
            ],
            'softref' => 'typolink'
        ]
    ],
    'tx_csseo_no_index' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_index',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
        ]
    ],
    'tx_csseo_no_index_method' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_no_index_method',
        'displayCond' => 'FIELD:tx_csseo_no_index:REQ:TRUE',
        'exclude' => 1,
        'config' => [
            'type' => 'select',
            'items' => [
                ['noindex,follow', 0],
                ['noindex,nofollow', 1],
            ]
        ]
    ],
    'tx_csseo_og_title' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_title',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '95',
            'eval' => 'trim',
        ]
    ],
    'tx_csseo_og_description' => [
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
    'tx_csseo_og_image' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_og_image',
        'l10n_mode' => 'exclude',
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
    'tx_csseo_tw_title' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_title',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '70',
            'eval' => 'trim',
        ]
    ],
    'tx_csseo_tw_description' => [
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
    'tx_csseo_tw_image' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_image',
        'l10n_mode' => 'exclude',
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
    'tx_csseo_tw_creator' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_creator',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '40',
            'eval' => 'trim',
        ]
    ],
];

// add new fields
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);

// replace description
$GLOBALS['TCA']['pages']['palettes']['metatags']['showitem'] =
    preg_replace('/description(.*,|.*$)/', '', $GLOBALS['TCA']['pages']['palettes']['metatags']['showitem']);

// define new palettes
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'tx_csseo_preview',
    'tx_csseo_title,tx_csseo_title_only,--linebreak--,
    description;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.description');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'tx_csseo_index',
    'tx_csseo_canonical,tx_csseo_no_index,tx_csseo_no_index_method');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'tx_csseo_facebook',
    'tx_csseo_og_title, --linebreak--,
    tx_csseo_og_description, --linebreak--,
    tx_csseo_og_image');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'tx_csseo_twitter',
    'tx_csseo_tw_title, --linebreak--,
    tx_csseo_tw_description, --linebreak--,
    tx_csseo_tw_image, --linebreak--,
    tx_csseo_tw_creator');

// add new fields to types
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo, 
    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_preview;tx_csseo_preview,tx_csseo_keyword,
    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_index;tx_csseo_index,
    --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.social,
    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_facebook;tx_csseo_facebook,
    --palette--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.palette.tx_csseo_twitter;tx_csseo_twitter',
    '1,6',
    'after:lastUpdated'
);

// add no_index to request update
$GLOBALS['TCA']['pages']['ctrl']['requestUpdate'] .= ',tx_csseo_no_index';

// register page TSconfig
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
	'cs_seo',
	'Configuration/TSconfig/news.ts',
	'EXT:cs_seo - Extend news reocrds');