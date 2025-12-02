<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class AfterTcaCompilationEventListener
{
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tcaBackup = $GLOBALS['TCA'];
        // ExtensionManagementUtility::addToAllTCAtypes() directly manipulates
        // $GLOBALS['TCA'] so we need to temporarily expose the compiled TCA this way
        $GLOBALS['TCA'] = $event->getTca();

        $this->addCsSeoFieldsToDoktypes();
        $this->addCsSeoMetadataFieldsToRecords();

        $event->setTca($GLOBALS['TCA']);
        $GLOBALS['TCA'] = $tcaBackup;
    }

    protected function addCsSeoFieldsToDoktypes(): void
    {
        // add new fields to pages
        ExtensionManagementUtility::addToAllTCAtypes(
            'pages',
            'tx_csseo_keyword',
            implode(',', ConfigurationUtility::getEvaluationDoktypes()),
            'after:canonical_link'
        );
    }

    protected function addCsSeoMetadataFieldsToRecords(): void
    {
        // Extend TCA of records like news etc.
        $tempColumns = [
            'tx_csseo' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_meta',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_csseo_domain_model_meta',
                    'foreign_field' => 'uid_foreign',
                    'foreign_table_field' => 'tablenames',
                    'maxitems' => 1,
                    'appearance' => [
                        'collapseAll' => false,
                        'showPossibleLocalizationRecords' => true,
                        'showSynchronizationLink' => true,
                    ],
                ],
            ],
        ];

        $tables = ConfigurationUtility::getTablesToExtend();

        if ($tables !== []) {
            foreach (array_keys($tables) as $tableName) {
                ExtensionManagementUtility::addTCAcolumns($tableName, $tempColumns);
                ExtensionManagementUtility::addToAllTCAtypes(
                    $tableName,
                    '--div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,tx_csseo'
                );
            }
        }
    }
}
