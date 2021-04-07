<?php

defined('TYPO3_MODE') || die('Access denied.');

$boot = function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_csseo_domain_model_meta',
        'EXT:cs_csseo/Resources/Private/Language/locallang_csh_tx_csseo_domain_model_meta.xlf'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_csseo_domain_model_meta');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'pages',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf'
    );

    if (TYPO3_MODE === 'BE') {
        /**
         * Include Backend Module
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'cs_seo',
            'web',
            'mod1',
            '',
            [
                \Clickstorm\CsSeo\Controller\ModuleWebController::class => 'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageResults, pageEvaluation'
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
                'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'cs_seo',
            'file',
            'mod_file',
            'bottom',
            [
                \Clickstorm\CsSeo\Controller\ModuleFileController::class => 'showEmptyImageAlt'
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
                'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf',
            ]
        );
    }
};

$boot();
unset($boot);
