<?php

namespace Clickstorm\CsSeo\Controller;

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

use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class TypoScriptFrontendController
 *
 */
class TypoScriptFrontendController extends \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
{
    /**
     * @override
     */
    public $showHiddenPage = true;

    /**
     * @override
     */
    public function getPageRenderer()
    {
        return $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
    }

    /**
     * page is in backend so found is true
     *
     * @param string $reason Reason text
     * @param string $header HTTP header to send
     *
     * @return void
     */
    public function pageNotFoundAndExit($reason = '', $header = '')
    {
        return;
    }

    /**
     * Get The Page ID
     * Override because force HTTPS is not necessary
     *
     * @throws ServiceUnavailableException
     * @return void
     * @access private
     */
    public function fetch_the_id()
    {
        $timeTracker = $this->getTimeTracker();
        $timeTracker->push('fetch_the_id initialize/', '');
        // Initialize the page-select functions.
        $this->sys_page = GeneralUtility::makeInstance(PageRepository::class, $this->context);
        // Set the valid usergroups for FE
        $this->initUserGroups();
        // Sets sys_page where-clause
        $this->setSysPageWhereClause();
        // Splitting $this->id by a period (.).
        // First part is 'id' and second part (if exists) will overrule the &type param
        $idParts = explode('.', $this->id, 2);
        $this->id = $idParts[0];
        if (isset($idParts[1])) {
            $this->type = $idParts[1];
        }

        // If $this->id is a string, it's an alias
        $this->checkAndSetAlias();
        // The id and type is set to the integer-value - just to be sure...
        $this->id = (int)$this->id;
        $this->type = (int)$this->type;
        $timeTracker->pull();
        // We find the first page belonging to the current domain
        $timeTracker->push('fetch_the_id domain/', '');
        // The page_id of the current domain
        if (!$this->id) {
            if ($this->domainStartPage) {
                // If the id was not previously set, set it to the id of the domain.
                $this->id = $this->domainStartPage;
            } else {
                // Find the first 'visible' page in that domain
                $rootLevelPages = $this->sys_page->getMenu([0], 'uid', 'sorting', '', false);
                if (!empty($rootLevelPages)) {
                    $theFirstPage = reset($rootLevelPages);
                    $this->id = $theFirstPage['uid'];
                } else {
                    $message = 'No pages are found on the rootlevel!';
                    $this->logger->alert($message);
                    try {
                        $response = GeneralUtility::makeInstance(ErrorController::class)->unavailableAction(
                            new ServerRequest(),
                            $message,
                            ['code' => PageAccessFailureReasons::NO_PAGES_FOUND]
                        );
                        throw new ImmediateResponseException($response, 1533931299);
                    } catch (ServiceUnavailableException $e) {
                        throw new ServiceUnavailableException($message, 1301648975);
                    }
                }
            }
        }
        $timeTracker->pull();
        $timeTracker->push('fetch_the_id rootLine/', '');
        // We store the originally requested id
        $this->requestedId = $this->id;
        $this->getPageAndRootlineWithDomain($this->domainStartPage);
        $timeTracker->pull();

        // Set no_cache if set
        if ($this->page['no_cache']) {
            $this->set_no_cache('no_cache is set in page properties');
        }
        // Init SYS_LASTCHANGED
        $this->register['SYS_LASTCHANGED'] = (int)$this->page['tstamp'];
        if ($this->register['SYS_LASTCHANGED'] < (int)$this->page['SYS_LASTCHANGED']) {
            $this->register['SYS_LASTCHANGED'] = (int)$this->page['SYS_LASTCHANGED'];
        }
        if (is_array($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['fetchPageId-PostProcessing'])) {
            foreach ($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['fetchPageId-PostProcessing'] as
                     $functionReference) {
                $parameters = ['parentObject' => $this];
                GeneralUtility::callUserFunction($functionReference, $parameters, $this);
            }
        }
    }

    /**
     * @return \TYPO3\CMS\Core\TimeTracker\TimeTracker
     */
    protected function getTimeTracker()
    {
        return $GLOBALS['TT'];
    }

    /**
     * Sets sys_page where-clause
     *
     * @return void
     * @access private
     */
    public function setSysPageWhereClause()
    {
        $this->sys_page->where_hid_del = '';
    }

    /**
     * @override
     */
    protected function initPageRenderer()
    {
        if ($this->pageRenderer !== null) {
            return;
        }
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    }
}
