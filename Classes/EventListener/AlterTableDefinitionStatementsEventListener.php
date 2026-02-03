<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

#[AsEventListener(
    identifier: 'cs-seo/alter-table-definition-statements',
    event: AlterTableDefinitionStatementsEvent::class,
    method: 'addMetadataDatabaseSchemaToTablesDefinition',
    after: 'addMysqlFulltextIndex'
)]
class AlterTableDefinitionStatementsEventListener
{
    /**
     * A slot method to inject the required tx_csseo database fields to the
     * tables definition string
     */
    public function addMetadataDatabaseSchemaToTablesDefinition(AlterTableDefinitionStatementsEvent $event): void
    {
        $config = ConfigurationUtility::getTablesToExtend();

        if ($config !== []) {
            foreach (array_keys($config) as $tableName) {
                $sqlString = str_repeat(PHP_EOL, 3) . 'CREATE TABLE ' . $tableName . ' (' . PHP_EOL
                    . ' tx_csseo int(11) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3);
                $event->addSqlData($sqlString);
            }
        }
    }
}
