<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'SEO');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages_language_overlay',
    'EXT:cs_seo/Resources/Private/Language/locallang_csh_pages.xlf');

/**
 * Include Backend Module
 * @todo remove condition for TYPO3 6.2 in upcoming major version
 */
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Clickstorm.' . $_EXTKEY,
        'web',
        'mod1',
        '',
        array(
            'Module' => 'pageMeta, pageOpenGraph, pageTwitter'
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/mod.' .
                (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0') ? 'svg' : 'png'),
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
        )
    );
}