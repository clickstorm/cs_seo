<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') || die();

ExtensionManagementUtility::addStaticFile('cs_seo', 'Configuration/TypoScript', 'SEO');

ExtensionManagementUtility::addStaticFile(
    'cs_seo',
    'Configuration/TypoScript/Additional/Tracking/GoogleTagManager/',
    'SEO - Google Tag Manager !bodyTagCObject'
);
