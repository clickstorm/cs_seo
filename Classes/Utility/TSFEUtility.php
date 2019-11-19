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

use Clickstorm\CsSeo\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
 *
 */
class TSFEUtility
{

    /**
     * @var int
     */
    protected $pageUid;

    /**
     * @var int
     */
    protected $workspaceUid;

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
     *
     * @param int $pageUid
     * @param int $lang
     * @param int $typeNum
     */
    public function __construct($pageUid, $lang = 0, $typeNum = 654)
    {
        $this->pageUid = $pageUid;
        $this->workspaceUid = $GLOBALS['BE_USER']->workspace ?: 0;
        $this->lang = is_array($lang) ? array_shift($lang) : $lang;
        $this->typeNum = $typeNum;

        $environmentService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\EnvironmentService::class);

        if (!isset($GLOBALS['TSFE'])
            || ($environmentService->isEnvironmentInBackendMode()
                && !($GLOBALS['TSFE']
                    instanceof
                    TypoScriptFrontendController))
        ) {
            $this->initTSFE();
        }
        $this->config = $GLOBALS['TSFE']->tmpl->setup['config.'];
    }

    /**
     * initialize the TSFE for the backend
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function initTSFE()
    {
        try {
            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class, false);
                $GLOBALS['TT']->start();
            }

            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                $this->pageUid,
                $this->typeNum
            );

            $GLOBALS['TSFE']->workspacePreview = $this->workspaceUid;
            $GLOBALS['TSFE']->forceTemplateParsing = true;
            $GLOBALS['TSFE']->showHiddenPages = true;
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->newCObj();

            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $this->lang;
            $GLOBALS['TSFE']->settingLanguage();

            $GLOBALS['TSFE']->preparePageContentGeneration();
        } catch (\Exception $e) {
            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                LocalizationUtility::translate('error.no_ts', 'cs_seo'),
                FlashMessage::ERROR
            );
            /** @var FlashMessageService $flashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier('cs_seo');
            $messageQueue->enqueue($message);
        }
    }

    /**
     * @return string
     */
    public function getPagePath()
    {
        $parameter = [
            'parameter' => $this->pageUid,
            'additionalParams' => '&L=' . (int)$this->lang,
            'forceAbsoluteUrl' => 1
        ];

        return $GLOBALS['TSFE']->cObj->typoLink_URL($parameter);
    }

    /**
     * @return array
     */
    public function getPage()
    {
        return $GLOBALS['TSFE']->page;
    }

    /**
     * @return array
     */
    public function getPageTitleFirst()
    {
        return $this->config['pageTitleFirst'];
    }

    /**
     * @var string $title
     * @var bool $title
     *
     * @return string
     */
    public function getFinalTitle($title, $titleOnly = false)
    {
        if ($titleOnly) {
            return $title;
        }

        $siteTitle = $this->getSiteTitle();
        $pageTitleFirst = $this->getConfig()['pageTitleFirst'];
        $pageTitleSeparator = $this->getPageTitleSeparator();

        if ($pageTitleFirst) {
            $title .= $pageTitleSeparator . $siteTitle;
        } else {
            $title = $siteTitle . $pageTitleSeparator . $title;
        }

        return $title;
    }

    /**
     * @return string
     */
    public function getSiteTitle()
    {
        $sitetitle = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['sitetitle'];
        if ($sitetitle) {
            return $GLOBALS['TSFE']->sL($sitetitle);
        } else {
            return $GLOBALS['TSFE']->tmpl->setup['sitetitle'];
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getPageTitleSeparator()
    {
        if (empty($GLOBALS['TSFE']->cObj)) {
            $GLOBALS['TSFE']->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        }
        
        return $GLOBALS['TSFE']->cObj->stdWrap(
            $this->config['pageTitleSeparator'],
            $this->config['pageTitleSeparator.']
        );
    }
}
