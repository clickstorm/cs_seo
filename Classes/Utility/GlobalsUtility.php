<?php

namespace Clickstorm\CsSeo\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;

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

    public static function getTYPO3Request(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    public static function getSite(int $pageId = 0): NullSite|Site|null
    {
        $site = self::getTYPO3Request()->getAttribute('site');

        if (!($site instanceof Site) && $pageId > 0) {
            try {
                $site = GeneralUtility::makeInstance(SiteFinder::class)
                    ->getSiteByPageId($pageId);
            } catch (SiteNotFoundException) {
                return null;
            }
        }

        return $site;
    }

    public static function getSiteLanguage(): SiteLanguage
    {
        return self::getTYPO3Request()->getAttribute('language');
    }

    public static function getLocale(): string
    {
        return self::getSiteLanguage()->getLocale();
    }

    public static function getTypoScriptSetup(): array
    {
        return self::getTYPO3Request()->getAttribute('frontend.typoscript')->getSetupArray();
    }

    public static function getPageInformation(): ?PageInformation
    {
        return self::getTYPO3Request()->getAttribute('frontend.page.information');
    }

    public static function getPageId(): int
    {
        return (int)self::getPageInformation()?->getId();
    }

    public static function getPageRecord(): ?array
    {
        return self::getPageInformation()?->getPageRecord();
    }
}
