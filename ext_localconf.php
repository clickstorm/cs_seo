<?php

use Clickstorm\CsSeo\Form\FieldWizard\CharCounterWizard;
use Clickstorm\CsSeo\Hook\PageHook;
use Clickstorm\CsSeo\Form\Element\SnippetPreview;
use Clickstorm\CsSeo\Form\Element\JsonLdElement;
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

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1767867975] = [
        'nodeName' => 'txCsseoCharCounter',
        'priority' => 30,
        'class' => CharCounterWizard::class,
    ];

    // Register the class to be available in 'eval' of TCA
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\JsonLdEvaluator'] = '';

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
