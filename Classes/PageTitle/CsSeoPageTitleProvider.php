<?php

namespace Clickstorm\CsSeo\PageTitle;

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the page title tag with cs_seo settings
 *
 * Class PageTitle
 */
class CsSeoPageTitleProvider extends AbstractPageTitleProvider
{
    protected ?TSFEUtility $TSFE = null;

    public function __construct()
    {
        // initialize TSFE
        $this->initialize();
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if ($metaData && $metaData['title']) {
            $this->title = $metaData['title'];
        }
    }

    protected function initialize(): void
    {
        // @extensionScannerIgnoreLine
        $this->TSFE = GeneralUtility::makeInstance(TSFEUtility::class, $GLOBALS['TSFE']->id);
    }

    protected function getPage(): array
    {
        return $this->TSFE->getPage();
    }
}
