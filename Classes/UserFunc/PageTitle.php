<?php
namespace Clickstorm\CsSeo\UserFunc;

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

use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the page title tag with cs_seo settings
 *
 * Class PageTitle
 *
 * @package Clickstorm\CsSeo\UserFunc
 */
class PageTitle
{

    /**
     * @var TSFEUtility
     */
    protected $TSFE;

    /**
     * @param string $oldTitle
     * @param array $content
     *
     * @return string
     */
    public function render($oldTitle, $content)
    {
        // initalize TSFE
        $this->initialize();

        // get all configurations
        $page = $this->getPage();

        // build the title
        $title = $page['tx_csseo_title']
            ?: $GLOBALS['TSFE']->altPageTitle
            ?: $page['title']
        ;

        $title = $this->TSFE->getFinalTitle($title, $page['tx_csseo_title_only']);

        return $title;
    }

    /**
     * Set the TSFE
     *
     * @return void
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
