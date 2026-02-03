<?php

namespace Clickstorm\CsSeo\UserFunc;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * UserFunc to provide items
 */
final readonly class Tca
{
    public function __construct(private SiteFinder $siteFinder) {}
    public function getLanguagesForXDefaultOnSiteConfig(array &$configuration): void
    {
        if (isset($configuration['row']['uid'])) {
            $siteRootPageUid = (int)$configuration['row']['uid'];

            try {
                $site = $this->siteFinder
                    ->getSiteByRootPageId($siteRootPageUid);
            } catch (SiteNotFoundException) {
                return;
            }

            // reset items
            $configuration['items'] = [];

            foreach ($site->getAllLanguages() as $language) {
                // @extensionScannerIgnoreLine
                $configuration['items'][] = [$language->getTitle(), $language->getLanguageId()];
            }
        }
    }
}
