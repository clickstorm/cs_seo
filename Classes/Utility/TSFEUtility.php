<?php

namespace Clickstorm\CsSeo\Utility;

use Clickstorm\CsSeo\Controller\TypoScriptFrontendController;

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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\TypoScriptAspect;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
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
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function __construct($pageUid, $lang = 0, $typeNum = 654)
    {
        $this->pageUid = $pageUid;
        $this->workspaceUid = $GLOBALS['BE_USER']->workspace ?? 0;
        $this->lang = (int)(is_array($lang) ? array_shift($lang) : $lang);
        $this->typeNum = $typeNum;

        if (!isset($GLOBALS['TSFE'])
            || ($this->isEnvironmentInBackendMode()
                && !($GLOBALS['TSFE']
                    instanceof
                    TypoScriptFrontendController))
        ) {
            $this->initTSFE();
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $fullTS = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $this->config = $fullTS['config.'];
    }

    /**
     * initialize the TSFE for the backend
     *
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function initTSFE()
    {
        try {
            if (!isset($GLOBALS['TT']) || !is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class, false);
                GeneralUtility::makeInstance(TimeTracker::class)->start();
            }

            $context = GeneralUtility::makeInstance(Context::class);
            $typoScriptAspect = GeneralUtility::makeInstance(TypoScriptAspect::class, true);
            $context->setAspect('typoscript', $typoScriptAspect);
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->pageUid);
            $pageArguments = $GLOBALS['TYPO3_REQUEST']->getAttribute('routing') ?? new PageArguments($this->pageUid, '0', []);
            $frontedUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
            $frontedUser->start();

            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $context,
                $site,
                $site->getLanguageById($this->lang),
                $pageArguments,
                $frontedUser
            );

            $GLOBALS['TSFE']->id = $this->pageUid;

            $GLOBALS['TSFE']->newCObj();
            $GLOBALS['TSFE']->determineId();

            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $this->lang;

            $GLOBALS['TSFE']->preparePageContentGeneration($GLOBALS['TYPO3_REQUEST']);
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
     * @return bool
     */
    public function getPageTitleFirst(): bool
    {
        return isset($this->config['pageTitleFirst']) ? (bool)$this->config['pageTitleFirst'] : false;
    }

    /**
     * @param string $title
     * @param bool $titleOnly
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

    public function getSiteTitle(): string
    {
        if (!isset($GLOBALS['TSFE']) || !is_object($GLOBALS['TSFE'])) {
            return '';
        }
        if ($GLOBALS['TSFE']->getLanguage() instanceof SiteLanguage
            && trim($GLOBALS['TSFE']->getLanguage()->getWebsiteTitle()) !== ''
        ) {
            return trim($GLOBALS['TSFE']->getLanguage()->getWebsiteTitle());
        }
        if ($GLOBALS['TSFE']->getSite() instanceof SiteInterface
            && isset($GLOBALS['TSFE']->getSite()->getConfiguration()['websiteTitle'])
            && trim($GLOBALS['TSFE']->getSite()->getConfiguration()['websiteTitle']) !== ''
        ) {
            return trim($GLOBALS['TSFE']->getSite()->getConfiguration()['websiteTitle']);
        }
        return '';
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
        if (!isset($GLOBALS['TSFE']) || !is_object($GLOBALS['TSFE'])) {
            return '';
        }

        return $GLOBALS['TSFE']->cObj->stdWrap(
            $this->config['pageTitleSeparator'],
            $this->config['pageTitleSeparator.']
        );
    }

    public function getPreviewSettings(): array
    {
        // preview settings
        $previewSettings = [];
        $previewSettings['siteTitle'] = $this->getSiteTitle();
        $previewSettings['pageTitleFirst'] = $this->getPageTitleFirst();
        $previewSettings['pageTitleSeparator'] = $this->getPageTitleSeparator();

        if ($previewSettings['pageTitleFirst']) {
            $previewSettings['siteTitle'] = $previewSettings['pageTitleSeparator'] . $previewSettings['siteTitle'];
        } else {
            $previewSettings['siteTitle'] .= $previewSettings['pageTitleSeparator'];
        }

        return $previewSettings;
    }

    public function isEnvironmentInBackendMode(): bool
    {
        return ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }
}
