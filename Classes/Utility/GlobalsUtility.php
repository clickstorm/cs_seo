<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
class GlobalsUtility
{
    /**
     * Returns the language service
     *
     * @return LanguageService
     */
    public static function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
