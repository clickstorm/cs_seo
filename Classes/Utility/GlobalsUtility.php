<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class GlobalsUtility
{
    public static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    public static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    public static function getTYPO3Request(): ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    public static function getTypoScriptSetup(): array
    {
        return self::getTYPO3Request()->getAttribute('frontend.typoscript')->getSetupArray();
    }

    public static function getPageId(): int
    {
        return self::getTYPO3Request()->getAttribute('frontend.page.information')->getId();
    }

    public static function getPageRecord(): array
    {
        return self::getTYPO3Request()->getAttribute('frontend.page.information')->getPageRecord();
    }
}
