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
        $tca = $event->getTca();
        $this->addCsSeoMetadataFieldsToRecords($tca);
        $event->setTca($tca);
    }

    protected function addCsSeoMetadataFieldsToRecords(&$tca): void
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
                // adds tx_csseo to columns of table
                if (is_array($tca[$tableName]['columns'] ?? false)) {
                    $tca[$tableName]['columns'] = array_merge($tca[$tableName]['columns'], $tempColumns);
                }
                // adds tab with tx_csseo to showitems for every type of table
                foreach ($tca[$tableName]['types'] as $type => &$typeDetails) {
                    $typeDetails['showitem'] = $typeDetails['showitem'] . ', --div--;LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:pages.tab.seo,tx_csseo';
                }
            }
        }
    }
}
