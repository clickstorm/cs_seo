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

class TSFE {

    protected $pageUid;

    protected $parentUid;

    protected $typeNum = 654;

    protected $config;

    public function __construct($pageUid, $lang = 0) {
        $this->pageUid = $pageUid;

        $this->lang = $lang;

        if(!isset($GLOBALS['TSFE'])) {
            $this->initTSFE();
        }

        $this->config = $GLOBALS['TSFE']->tmpl->setup['config.'];
    }

    public function getPagePath() {
        return $GLOBALS['TSFE']->cObj->getTypoLink_URL($this->pageUid);
    }

    public function getPage() {
        return $GLOBALS['TSFE']->page;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getPageTitleSeparator() {
        return $GLOBALS['TSFE']->cObj->stdWrap($this->config['pageTitleSeparator'], $this->config['pageTitleSeparator.']);
    }

    public function getSiteTitle() {
        return $GLOBALS['TSFE']->tmpl->setup['sitetitle'];
    }

    /**
     * @param int $pageUid
     * @param int $typeNum
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