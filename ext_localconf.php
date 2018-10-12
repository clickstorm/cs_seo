<?php
defined('TYPO3_MODE') || die('Access denied.');

if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
    $confArray = \Clickstorm\CsSeo\Utility\ConfigurationUtility::getEmConfiguration();

    if (TYPO3_MODE === 'BE') {

        // Hook into the page module
        if (!isset($confArray['inPageModule']) || $confArray['inPageModule'] < 2) {
            $hook = ($confArray['inPageModule'] == 1) ? 'drawFooterHook' : 'drawHeaderHook';

            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php'][$hook][$_EXTKEY] =
                \Clickstorm\CsSeo\Hook\PageHook::class . '->render';
        }

        // add scheduler task
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$_EXTKEY] =
            \Clickstorm\CsSeo\Command\EvaluationCommandController::class;

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

    // add hook to get current cHash params
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['getData'][$_EXTKEY] =
        \Clickstorm\CsSeo\Hook\CurrentUrlGetDataHook::class;
}

// upgrade wizard
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\Clickstorm\CsSeo\Updates\PagesUpdater::$identifier]
    = \Clickstorm\CsSeo\Updates\PagesUpdater::class;

// remove canonical hreflang generator
// @TODO: remove when https://forge.typo3.org/issues/86577 is fixed
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] =
    array_diff(
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'],
        [
            \TYPO3\CMS\Seo\Canonical\CanonicalGenerator::class . '->generate',
            \TYPO3\CMS\Seo\HrefLang\HrefLangGenerator::class . '->generate'
        ]
);

// generate and overwrite header data
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
    \Clickstorm\CsSeo\Hook\MetaTagGeneratorHook::class . '->generate';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
    \Clickstorm\CsSeo\Hook\CanonicalAndHreflangHook::class . '->generate';


/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

// add tx_csseo fields to extended tables
$signalSlotDispatcher->connect(
    'TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService',
    'tablesDefinitionIsBeingBuilt',
    \Clickstorm\CsSeo\Hook\SqlExpectedSchemaHook::class,
    'addMetadataDatabaseSchemaToTablesDefinition'
);