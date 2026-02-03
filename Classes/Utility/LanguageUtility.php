<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Site\Entity\Site;

class LanguageUtility
{
    /**
     * check if the current backend user has access to this language
     *
     * @param int $pageId
     * @return array
     */
    public static function getLanguagesInBackend(int $pageId = 0): array
    {
        $languages[0] = 'Default';

        if ($pageId === 0) {
            return $languages;
        }

        $site = GlobalsUtility::getSite($pageId);

        if (!($site instanceof Site)) {
            return $languages;
        }

        foreach ($site->getAvailableLanguages(GlobalsUtility::getBackendUser()) as $language) {
            // @extensionScannerIgnoreLine
            $languages[$language->getLanguageId()] = $language->getTitle();
        }

        return $languages;
    }

    /**
     * check if the current language is available and enabled in the site settings
     *
     * @param int $languageId
     * @param int $pageId
     * @return bool
     */
    public static function isLanguageEnabled(int $languageId, int $pageId): bool
    {
        $siteLanguageIsAvailable = false;
        $site = GlobalsUtility::getSite($pageId);

        if ($site instanceof Site) {
            foreach ($site->getLanguages() as $siteLanguage) {
                // @extensionScannerIgnoreLine
                if ($siteLanguage->getLanguageId() === $languageId) {
                    $siteLanguageIsAvailable = true;
                    break;
                }
            }

        }

        return $siteLanguageIsAvailable;
    }
}
