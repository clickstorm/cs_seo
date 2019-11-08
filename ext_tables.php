<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_csseo_domain_model_meta',
    'EXT:cs_csseo/Resources/Private/Language/locallang_csh_tx_csseo_domain_model_meta.xlf'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_csseo_domain_model_meta');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'pages',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf'
);

/**
 * Include Backend Module
 */
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Clickstorm.' . $_EXTKEY,
        'web',
        'mod1',
        '',
        [
            'Module' => 'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageResults, pageEvaluation'
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/mod.svg',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
        ]
    );
}
