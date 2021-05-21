<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('cs_seo', 'Configuration/TypoScript', 'SEO');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'cs_seo',
    'Configuration/TypoScript/Additional/Tracking/GoogleTagManager/',
    'SEO - Google Tag Manager !bodyTagCObject'
);
