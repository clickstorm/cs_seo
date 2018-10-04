<?php

namespace Clickstorm\CsSeo\Hook;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class SqlExpectedSchemaHook
{
    /**
     * A slot method to inject the required tx_csseo database fields to the
     * tables definition string
     *
     * @param array $sqlString
     * @return array
     */
    public function addMetadataDatabaseSchemaToTablesDefinition(array $sqlString)
    {
        $tables = ConfigurationUtility::getTablesToExtend();
        if ($tables) {
            foreach ($tables as $table) {
                $sqlString[] = str_repeat(PHP_EOL, 3) . 'CREATE TABLE ' . $table . ' (' . PHP_EOL
                    . ' tx_csseo int(11) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3);
            }
        }

        return ['sqlString' => $sqlString];
    }
}