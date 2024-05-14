<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Hook\PageHook;
use Clickstorm\CsSeo\Form\Element\SnippetPreview;
use Clickstorm\CsSeo\Form\Element\JsonLdElement;
use Clickstorm\CsSeo\Hook\CurrentUrlGetDataHook;
use Clickstorm\CsSeo\Hook\MetaTagGeneratorHook;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') || die();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsDisallowAllEvaluator'] = '';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsExistsEvaluator'] = '';

    // new field types
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1524490066] = [
        'nodeName' => 'snippetPreview',
        'priority' => 30,
        'class' => SnippetPreview::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1620117622] = [
        'nodeName' => 'txCsseoJsonLd',
        'priority' => 30,
        'class' => JsonLdElement::class,
    ];

    // Register the class to be available in 'eval' of TCA
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\JsonLdEvaluator'] = '';

    // add hook to get current cHash params
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['getData']['cs_seo'] =
        CurrentUrlGetDataHook::class;

    // generate and overwrite header data
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        MetaTagGeneratorHook::class . '->generate';

    // Add module configuration
    ExtensionManagementUtility::addTypoScriptSetup(trim('
        config.pageTitleProviders {
            csSeo {
                provider = Clickstorm\CsSeo\PageTitle\CsSeoPageTitleProvider
                before = seo
            }
        }
    '));
})();
