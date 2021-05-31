<?php

namespace Clickstorm\CsSeo\Utility;

class GlobalsUtility
{
    /**
     * Returns the language service
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    public static function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    public static function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
