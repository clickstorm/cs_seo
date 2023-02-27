<?php

namespace Clickstorm\CsSeo\Hook;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class SqlExpectedSchemaHook
{
    /**
     * A slot method to inject the required tx_csseo database fields to the
     * tables definition string
     *
     * @param AlterTableDefinitionStatementsEvent $event
     */
    public function addMetadataDatabaseSchemaToTablesDefinition(AlterTableDefinitionStatementsEvent $event)
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
