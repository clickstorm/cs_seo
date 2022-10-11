<?php

namespace Clickstorm\CsSeo\PageTitle;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
    /**
     * @var TSFEUtility
     */
    protected $TSFE;

    public function __construct()
    {
        // initalize TSFE
        $this->initialize();
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if ($metaData && $metaData['title']) {
            // update title for indexed search
            $GLOBALS['TSFE']->indexedDocTitle = $metaData['title'];

            $this->title = $metaData['title'];
        }
    }

    /**
     * Set the TSFE
     */
    protected function initialize()
    {
        $this->TSFE = GeneralUtility::makeInstance(TSFEUtility::class, $GLOBALS['TSFE']->id);
    }

    /**
     * @return array
     */
    protected function getPage()
    {
        return $this->TSFE->getPage();
    }
}
