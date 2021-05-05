<?php
defined('TYPO3_MODE') or die();

(function () {
    if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        $confArray = \Clickstorm\CsSeo\Utility\ConfigurationUtility::getEmConfiguration();

        if (TYPO3_MODE === 'BE') {

            // Hook into the page module
            if (!isset($confArray['inPageModule']) || $confArray['inPageModule'] < 2) {
                $hook = ($confArray['inPageModule'] == 1) ? 'drawFooterHook' : 'drawHeaderHook';

                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php'][$hook]['cs_seo'] =
                    \Clickstorm\CsSeo\Hook\PageHook::class . '->render';
            }

            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsDisallowAllEvaluator'] = '';
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsExistsEvaluator'] = '';
        }

        // extend records
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['cs_seo'] =
            \Clickstorm\CsSeo\Hook\TableConfigurationPostProcessingHook::class;

        // new field types
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1524490067] = [
            'nodeName' => 'snippetPreview',
            'priority' => 30,
            'class' => \Clickstorm\CsSeo\Form\Element\SnippetPreview::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1620117622] = [
            'nodeName' => 'txCsseoJsonLd',
            'priority' => 30,
            'class' => \Clickstorm\CsSeo\Form\Element\JsonLdElement::class,
        ];

        // Register the class to be available in 'eval' of TCA
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\JsonLdEvaluator'] = '';

        // add hook to get current cHash params
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['getData']['cs_seo'] =
            \Clickstorm\CsSeo\Hook\CurrentUrlGetDataHook::class;
    }

    // generate and overwrite header data
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        \Clickstorm\CsSeo\Hook\MetaTagGeneratorHook::class . '->generate';

    // Add module configuration
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
        config.pageTitleProviders {
            csSeo {
                provider = Clickstorm\CsSeo\PageTitle\CsSeoPageTitleProvider
                before = seo
            }
        }
    '));
})();
