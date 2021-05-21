<?php

defined('TYPO3') or die();

(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_csseo_domain_model_meta',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_tx_csseo_domain_model_meta.xlf'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        '_MOD_txcsseo',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_mod.xlf'
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
                \Clickstorm\CsSeo\Controller\ModuleWebController::class =>
                    'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageStructuredData, pageResults, pageEvaluation'
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
                'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/web.xlf',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'cs_seo',
            'file',
            'mod_file',
            'bottom',
            [
                \Clickstorm\CsSeo\Controller\ModuleFileController::class => 'showEmptyImageAlt,update'
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:cs_seo/Resources/Public/Icons/modFile.svg',
                'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/file.xlf',
            ]
        );
    }
})();
