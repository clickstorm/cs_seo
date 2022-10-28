<?php

namespace Clickstorm\CsSeo\UserFunc;

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * UserFunc to provide items
 */
final class Tca
{
    public function getLanguagesForXDefaultOnSiteConfig(array &$configuration): void
    {
        if (isset($configuration['row']['uid'])) {
            $siteRootPageUid = (int)$configuration['row']['uid'];

            try {
                $site = GeneralUtility::makeInstance(SiteFinder::class)
                    ->getSiteByRootPageId($siteRootPageUid);
            } catch (SiteNotFoundException $exception) {
                return;
            }

            // reset items
            $configuration['items'] = [];

            foreach ($site->getAllLanguages() as $language) {
                $configuration['items'][] = [$language->getTitle(), $language->getLanguageId()];
            }
        }
    }
}
