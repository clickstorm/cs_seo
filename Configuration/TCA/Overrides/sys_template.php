<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('cs_seo', 'Configuration/TypoScript', 'SEO');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
	'cs_seo',
	'Configuration/TypoScript/Extensions/News',
	'SEO - Sitemap.xml for News'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cs_seo',
    'Configuration/TypoScript/Additional/Tracking/GoogleTagManager/',
    'SEO - Google Tag Manager !bodyTagCObject'
);
 