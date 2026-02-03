<?php

namespace Clickstorm\CsSeo\UserFunc;

use Clickstorm\CsSeo\Service\FrontendConfigurationService;
use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Modify the page title and may remove the website title
 */
final class PageTitle
{
    protected ?FrontendConfigurationService $frontendConfigurationService = null;

    /**
     * check the settings and may remove the suffix or prefix from the page title
     */
    #[AsAllowedCallable]
    public function resetWebsiteTitle(string $content, array $conf): string
    {
        return $this->showTitleOnly() ? $this->getCachedTitleWithoutWebsiteTitle($content) : $content;
    }

    protected function showTitleOnly(): bool
    {
        // first check if record is shown
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if (is_array($metaData) && !empty($metaData['title_only'])) {
            return true;
        }

        // check page settings
        $page = $this->getPage();

        return !empty($page['tx_csseo_title_only']);
    }

    /**
     * page without website title is stored in cache, see TSFE
     */
    protected function getCachedTitleWithoutWebsiteTitle(string $oldTitle = ''): string
    {
        $controller = GlobalsUtility::getTYPO3Request()->getAttribute('frontend.controller');
        $pageTitleCache = $this->getCache($controller) ?? [];
        if (!empty($pageTitleCache)) {
            return reset($controller->config['pageTitleCache']);
        }

        return $oldTitle;
    }

    /**
     * @return array
     */
    protected function getPage(): array
    {
        return GlobalsUtility::getPageRecord();
    }

    /**
     * @param mixed $controller
     * @return mixed
     */
    public function getCache(mixed $controller): mixed
    {
        return $controller->config['pageTitleCache'] ?? null;
    }
}
