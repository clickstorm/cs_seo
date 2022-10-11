<?php

namespace Clickstorm\CsSeo\UserFunc;

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Modify the page title and may remove the website title
 */
final class PageTitle
{
    /**
     * @var TSFEUtility
     */
    protected $TSFEUtility;

    /**
     * check the settings and may remove the suffix or prefix from the page title
     *
     * @param string $content When custom methods are used for data processing (like in stdWrap functions), the $content variable will hold the value to be processed. When methods are meant to just return some generated content (like in USER and USER_INT objects), this variable is empty.
     * @param array $conf TypoScript properties passed to this method.
     * @return string The input string reversed. If the TypoScript property "uppercase" was set, it will also be in uppercase. May also be linked.
     */
    public function resetWebsiteTitle(string $content, array $conf): string
    {
        // initalize TSFE
        $this->initialize();

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
     *
     * @param string $oldTitle
     * @return string
     */
    protected function getCachedTitleWithoutWebsiteTitle(string $oldTitle = ''): string
    {
        $pageTitleCache = $this->getTypoScriptFrontendController()->config['config']['pageTitleCache'] ?? [];
        if (!empty($pageTitleCache)) {
            return reset($this->getTypoScriptFrontendController()->config['config']['pageTitleCache']);
        }

        return $oldTitle;
    }

    /**
     * Set the TSFE
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->TSFEUtility = GeneralUtility::makeInstance(TSFEUtility::class, $GLOBALS['TSFE']->id);
    }

    /**
     * @return array
     */
    protected function getPage(): array
    {
        return $this->TSFEUtility->getPage();
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
