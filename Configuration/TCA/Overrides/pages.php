<?php

defined('TYPO3_MODE') or die();

// get extension configurations
$extConf = \Clickstorm\CsSeo\Utility\ConfigurationUtility::getEmConfiguration();

// SEO Settings
$GLOBALS['TCA']['pages']['columns']['title']['config']['max'] = $extConf['maxTitle'];
$GLOBALS['TCA']['pages']['columns']['nav_title']['config']['max'] = $extConf['maxNavTitle'];
$GLOBALS['TCA']['pages']['columns']['description']['config']['max'] = $extConf['maxDescription'];

$GLOBALS['TCA']['pages']['columns']['seo_title']['config']['max'] = $extConf['maxTitle'];
$GLOBALS['TCA']['pages']['columns']['seo_title']['config']['renderType'] = 'snippetPreview';

$GLOBALS['TCA']['pages']['columns']['no_index']['onChange'] = 'reload';

// define new fields
$tempColumns = [
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
    'tx_csseo_tw_creator' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_creator',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '40',
            'eval' => 'trim',
        ]
    ],
    'tx_csseo_tw_site' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_site',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '40',
            'eval' => 'trim',
        ]
    ],
    'tx_csseo_json_ld' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_json_ld',
        'exclude' => 1,
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 5,
            'eval' => 'trim'
        ]
    ],
];

// add new fields
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);

// replace description
$GLOBALS['TCA']['pages']['palettes']['metatags']['showitem'] =
    preg_replace('/description(.*,|.*$)/', '', $GLOBALS['TCA']['pages']['palettes']['metatags']['showitem']);

foreach ($GLOBALS['TCA']['pages']['types'] as $key => $type) {
    $type['showitem'] =
        str_replace('--palette--;;opengraph,', '', $type['showitem']);
    $GLOBALS['TCA']['pages']['types'][$key]['showitem'] =
        str_replace('--palette--;;twittercards,', '', $type['showitem']);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'metatags',
    'tx_csseo_json_ld',
    'before:keywords'
);

// define new palettes
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'seo',
    'description;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.description'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'twittercards',
    '--linebreak--,
    tx_csseo_tw_creator, tx_csseo_tw_site'
);
