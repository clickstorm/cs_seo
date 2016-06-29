<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 09:18
 */

namespace Clickstorm\CsSeo\Utility;

use \Clickstorm\CsSeo\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
 * @package Clickstorm\CsSeo\Utility
 */
class TSFEUtility {

    /**
     * @var int
     */
    protected $pageUid;

    /**
     * @var int
     */
    protected $parentUid;

    /**
     * @var \TYPO3\CMS\Extbase\Service\EnvironmentService
     */
    protected $environmentService;

    /**
     * @var int
     */
    protected $typeNum = 654;

    /**
     * @var array
     */
    protected $config;

    /**
     * TSFEUtility constructor.
     * @param int $pageUid
     * @param int $lang
     */
    public function __construct($pageUid, $lang = 0) {
        $this->pageUid = $pageUid;
        $this->lang = $lang;

        $environmentService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\EnvironmentService::class);
        if(!isset($GLOBALS['TSFE']) || ($environmentService->isEnvironmentInBackendMode() && !($GLOBALS['TSFE'] instanceof TypoScriptFrontendController))) {
            $this->initTSFE();
        }

        $this->config = $GLOBALS['TSFE']->tmpl->setup['config.'];
    }

    /**
     * @return string
     */
    public function getPagePath() {
        return $GLOBALS['TSFE']->cObj->getTypoLink_URL($this->pageUid);
    }

    /**
     * @return array
     */
    public function getPage() {
        return $GLOBALS['TSFE']->page;
    }


    /**
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getPageTitleFirst() {
        return $this->config['pageTitleFirst'];
    }

    /**
     * @return string
     */
    public function getPageTitleSeparator() {
        return $GLOBALS['TSFE']->cObj->stdWrap($this->config['pageTitleSeparator'], $this->config['pageTitleSeparator.']);
    }

    /**
     * @return string
     */
    public function getSiteTitle() {
        return $GLOBALS['TSFE']->tmpl->setup['sitetitle'];
    }

    /**
     * @return string
     */
    public function getFinalTitle($title) {
        $siteTitle = $this->getSiteTitle();
        $pageTitleFirst = $this->getConfig()['pageTitleFirst'];
        $pageTitleSeparator = $this->getPageTitleSeparator();
        if($pageTitleFirst) {
            $title .= $pageTitleSeparator . $siteTitle;
        } else {
            $title = $siteTitle . $pageTitleSeparator . $title;
        }
        return $title;
    }

    /**
     * initialize the TSFE for the backend
     *
     * @return void
     */
    protected function initTSFE() {
        try {
            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = new NullTimeTracker;
                $GLOBALS['TT']->start();
            }
            /** @var $frontend TypoScriptFrontendController */
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class,  $GLOBALS['TYPO3_CONF_VARS'], $this->pageUid, $this->typeNum);
            $GLOBALS['TSFE']->connectToDB();
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->newCObj();

            if($this->lang > 0) {
                $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $this->lang;
                $GLOBALS['TSFE']->settingLanguage();
            }



            if (ExtensionManagementUtility::isLoaded('realurl')) {
                $rootline = BackendUtility::BEgetRootLine($this->pageUid);
                $host = BackendUtility::firstDomainRecord($rootline);
                $_SERVER['HTTP_HOST'] = $host;
            }

        } catch (\Exception $e) {
            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($e);
            return;
        }
    }
}