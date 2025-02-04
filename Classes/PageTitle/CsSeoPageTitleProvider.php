<?php

namespace Clickstorm\CsSeo\PageTitle;

use Clickstorm\CsSeo\Service\MetaDataService;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the page title tag with cs_seo settings
 *
 * Class PageTitle
 */
class CsSeoPageTitleProvider extends AbstractPageTitleProvider
{
    public function __construct()
    {
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if ($metaData && $metaData['title']) {
            $this->title = strip_tags($metaData['title']);
        }
    }
}
