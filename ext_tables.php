<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Clickstorm\CsSeo\Controller\ModuleWebController;
use Clickstorm\CsSeo\Controller\ModuleFileController;
defined('TYPO3') || die();

(function () {
    ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_csseo_domain_model_meta',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_tx_csseo_domain_model_meta.xlf'
    );

    ExtensionManagementUtility::addLLrefForTCAdescr(
        '_MOD_txcsseo',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_mod.xlf'
    );

    ExtensionManagementUtility::allowTableOnStandardPages('tx_csseo_domain_model_meta');

    ExtensionManagementUtility::addLLrefForTCAdescr(
        'pages',
        'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf'
    );

    /**
     * Include Backend Module
     */
    ExtensionUtility::registerModule(
        'cs_seo',
        'web',
        'mod1',
        '',
        [
            ModuleWebController::class =>
                'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageStructuredData, pageResults, pageEvaluation',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
            'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/web.xlf',
        ]
    );

    ExtensionUtility::registerModule(
        'cs_seo',
        'file',
        'mod_file',
        'bottom',
        [
            ModuleFileController::class => 'showEmptyImageAlt,update',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:cs_seo/Resources/Public/Icons/modFile.svg',
            'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/file.xlf',
        ]
    );
})();
