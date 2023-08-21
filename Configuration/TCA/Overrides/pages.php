<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

// get extension configurations
$extConf = ConfigurationUtility::getEmConfiguration();

// SEO Settings
$GLOBALS['TCA']['pages']['columns']['title']['config']['max'] = $extConf['maxTitle'] ?? '';
$GLOBALS['TCA']['pages']['columns']['nav_title']['config']['max'] = $extConf['maxNavTitle'] ?? '';
$GLOBALS['TCA']['pages']['columns']['description']['config']['max'] = $extConf['maxDescription'] ?? '';

if (!empty($extConf['forceMinDescription'])) {
    $GLOBALS['TCA']['pages']['columns']['description']['config']['min'] = $extConf['minDescription'] ?? '';
}

$GLOBALS['TCA']['pages']['columns']['seo_title']['config']['max'] = $extConf['maxTitle'] ?? '';
$GLOBALS['TCA']['pages']['columns']['seo_title']['config']['renderType'] = 'snippetPreview';

$GLOBALS['TCA']['pages']['columns']['no_index']['onChange'] = 'reload';

// define new fields
$tempColumns = [
    'tx_csseo_title_only' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_title_only',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
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
    'tx_csseo_tw_creator' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_creator',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '40',
            'eval' => 'trim',
        ],
    ],
    'tx_csseo_tw_site' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_tw_site',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => '40',
            'eval' => 'trim',
        ],
    ],
    'tx_csseo_json_ld' => [
        'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tx_csseo_json_ld',
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
];

// add new fields
ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);

// add field tx_csseo_keyword to pages
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_csseo_keyword',
    implode(',', ConfigurationUtility::getEvaluationDoktypes()),
    'after:canonical_link'
);

// replace description
$GLOBALS['TCA']['pages']['palettes']['metatags']['showitem'] =
    preg_replace('/description(.*,|.*$)/', '', $GLOBALS['TCA']['pages']['palettes']['metatags']['showitem']);

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'metatags',
    'tx_csseo_json_ld',
    'before:keywords'
);

// define new palettes
ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'seo',
    'tx_csseo_title_only,--linebreak--,
    description;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.description',
    'after:seo_title'
);

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'twittercards',
    '--linebreak--,
    tx_csseo_tw_creator, tx_csseo_tw_site'
);


if (!empty($extConf['showDescriptionsInTCA'])) {
    // add descriptions
    $colsWithDescription = [
        'canonical_link',
        'description',
        'no_follow',
        'no_index',
        'og_description',
        'og_image',
        'og_title',
        'seo_title',
        'twitter_card',
        'twitter_description',
        'twitter_image',
        'twitter_title',
        'tx_csseo_json_ld',
        'tx_csseo_keyword',
        'tx_csseo_title_only',
        'tx_csseo_tw_creator',
        'tx_csseo_tw_site',
    ];

    foreach ($colsWithDescription as $col) {
        $GLOBALS['TCA']['pages']['columns'][$col]['description'] =
            'LLL:EXT:cs_seo/Resources/Private/Language/de.locallang_csh_pages.xlf:' . $col . '.description';
    }
}

