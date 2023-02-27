<?php

namespace Clickstorm\CsSeo\Utility;

use Clickstorm\CsSeo\Controller\TypoScriptFrontendController;

use Psr\Http\Message\ServerRequestInterface;
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
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Core\TypoScript\TemplateService;

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

    protected $cObj;

    /**
     * TSFEUtility constructor.
     *
     * @param int $pageUid
     * @param array|int $lang
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
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->config = $GLOBALS['TSFE']->config['config'];
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

            // generate needed parameters
            $context = GeneralUtility::makeInstance(Context::class);
            $typoScriptAspect = GeneralUtility::makeInstance(TypoScriptAspect::class, true);
            $context->setAspect('typoscript', $typoScriptAspect);
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->pageUid);
            $pageArguments = new PageArguments($this->pageUid, '0', []);
            $frontedUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
            $frontedUser->start($GLOBALS['TYPO3_REQUEST']);

            // new TSFE instance
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $context,
                $site,
                $site->getLanguageById($this->lang),
                $pageArguments,
                $frontedUser
            );

            // @extensionScannerIgnoreLine
            $GLOBALS['TSFE']->id = $this->pageUid;

            $GLOBALS['TSFE']->newCObj($GLOBALS['TYPO3_REQUEST']);
            $GLOBALS['TSFE']->determineId($GLOBALS['TYPO3_REQUEST']);

            // get TypoScript - see https://www.in2code.de/aktuelles/php-typoscript-im-backend-oder-command-kontext-nutzen/
            $rootlineUtil = GeneralUtility::makeInstance(RootlineUtility::class, $this->pageUid);
            $templateService = GeneralUtility::makeInstance(TemplateService::class);

            $rootLine = $rootlineUtil->get();

            $templateService->runThroughTemplates($rootLine);

            $templateService->generateConfig();

            // set TypoScript
            $GLOBALS['TSFE']->config['config'] = $templateService->setup['config.'] ?? [];
            $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $this->lang;
        } catch (\Exception $e) {
            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                LocalizationUtility::translate('error.no_ts', 'cs_seo'),
                ContextualFeedbackSeverity::ERROR
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
            'forceAbsoluteUrl' => 1,
        ];

        return $this->cObj->typoLink_URL($parameter);
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

        return $this->cObj->stdWrap(
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
