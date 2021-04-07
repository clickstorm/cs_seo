<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileUtility
{
    /**
     * @param string $combinedIdentifier e.g. 1:/user_upload/
     * @return int
     */
    public static function getStorageUidFromCombinedIdentifier(string $combinedIdentifier = ''): int
    {
        $parts = GeneralUtility::trimExplode(':', $combinedIdentifier);

        return count($parts) === 2 ? (int)$parts[0] : 0;
    }

    /**
     * @param string $combinedIdentifier e.g. 1:/user_upload/
     * @return string
     */
    public static function getIdentifierFromCombinedIdentifier(string $combinedIdentifier = ''): string
    {
        return substr($combinedIdentifier, strpos($combinedIdentifier, ':') + 1);
    }


}
