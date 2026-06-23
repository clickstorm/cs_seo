<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Hook;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Normalizes the ALT text of sys_file_reference and sys_file_metadata records before saving.
 *
 * Redactors may edit the field as a multiline textarea in the backend.
 * This hook ensures that line breaks and repeated whitespace are collapsed
 * into a single space so the persisted value is always single-line.
 *
 * Can be disabled via Extension Manager: cs_seo → enableMultilineAltText = 0
 */
final class DataHandlerAltTextHook
{
    /**
     * DataHandler hook: called before field values are written to the database.
     *
     * @param array<string, mixed> $incomingFieldArray
     * @param int|string $id
     */
    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        string $table,
        $id,
        DataHandler $dataHandler
    ): void {
        $affectedTables = ['sys_file_reference', 'sys_file_metadata'];

        if (!in_array($table, $affectedTables, true)) {
            return;
        }

        if (!array_key_exists('alternative', $incomingFieldArray)) {
            return;
        }

        $extConf = ConfigurationUtility::getEmConfiguration();
        if (empty($extConf['enableMultilineAltText'])) {
            return;
        }

        $incomingFieldArray['alternative'] = self::normalize((string)($incomingFieldArray['alternative'] ?? ''));
    }

    /**
     * Replaces any line breaks / tabs / repeated whitespace with a single space
     * and trims the result.
     */
    public static function normalize(string $value): string
    {
        $value = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $value);
        $value = (string)preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }
}

