<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'SEO');

    },
    $_EXTKEY
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages_language_overlay',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');