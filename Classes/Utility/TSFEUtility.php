<?php
namespace Clickstorm\CsSeo\Utility;

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

use \Clickstorm\CsSeo\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
    protected $typeNum;

	/**
	 * @var int
	 */
	protected $lang;

    /**
     * @var array
     */
    protected $config;

    /**
     * TSFEUtility constructor.
     * @param int $pageUid
     * @param int $lang
     * @param int $typeNum
     */
    public function __construct($pageUid, $lang = 0, $typeNum = 654) {
        $this->pageUid = $pageUid;
        $this->lang = is_array($lang) ? array_shift($lang) : $lang;
	    $this->typeNum = $typeNum;

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
    	$params = ($this->lang > 0) ? ['L' => $this->lang] : [];
        return $GLOBALS['TSFE']->cObj->getTypoLink_URL($this->pageUid, $params);
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
	    $sitetitle = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['sitetitle'];
//	    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($GLOBALS['TSFE']);
	    if($sitetitle) {
		    return $GLOBALS['TSFE']->sL($sitetitle);
	    } else {
		    return $GLOBALS['TSFE']->tmpl->setup['sitetitle'];
	    }
    }

    /**
     * @var string $title
     * @var bool $title
     *
     * @return string
     */
    public function getFinalTitle($title, $titleOnly = false) {
    	if($titleOnly) {
	        return $title;
    	}

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
	        GeneralUtility::_GETset($this->lang, 'L');
            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = new NullTimeTracker;
                $GLOBALS['TT']->start();
            }

            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'], $this->pageUid, $this->typeNum);

            $GLOBALS['TSFE']->config = [];
            $GLOBALS['TSFE']->forceTemplateParsing = true;

            $GLOBALS['TSFE']->connectToDB();
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->newCObj();


            if (ExtensionManagementUtility::isLoaded('realurl')) {
                $rootline = BackendUtility::BEgetRootLine($this->pageUid);
                $host = BackendUtility::firstDomainRecord($rootline);
                $_SERVER['HTTP_HOST'] = $host;
            }

            $GLOBALS['TSFE']->getConfigArray();
	        $GLOBALS['TSFE']->settingLanguage();
        } catch (\Exception $e) {
            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($e);
            return;
        }
    }
}