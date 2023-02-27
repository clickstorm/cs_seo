<?php

namespace Clickstorm\CsSeo\Controller;

use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TypoScriptFrontendController
 */
class TypoScriptFrontendController extends \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
{
    /**
     * @override
     */
    public $showHiddenPage = true;

    protected function getTimeTracker(): TimeTracker
    {
        return $GLOBALS['TT'];
    }

    /**
     * Sets sys_page where-clause
     */
    public function setSysPageWhereClause(): void
    {
        $this->sys_page->where_hid_del = '';
        $this->sys_page->where_groupAccess = '';
    }
}
