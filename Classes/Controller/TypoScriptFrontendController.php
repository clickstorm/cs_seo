<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 10:49
 */

namespace Clickstorm\CsSeo\Controller;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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