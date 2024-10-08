<?php

namespace Clickstorm\CsSeo\Utility;

use In2code\Powermail\Utility\BackendUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\TypoScriptAspect;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * own TSFE to render TSFE in the backend
 *
 * Class TSFEUtility
 */
class TSFEUtility
{
    protected int $pageUid = 0;

    protected int $workspaceUid = 0;

    protected int $parentUid = 0;

    protected int $typeNum = 0;

    protected int $lang = 0;

    protected array $config = [];

    protected ?ContentObjectRenderer $cObj = null;

    /**
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function __construct(int $pageUid, int $lang = 0, int $typeNum = 654)
    {
        $this->pageUid = $pageUid;
        $this->workspaceUid = $GLOBALS['BE_USER']->workspace ?? 0;
        $this->lang = (int)(is_array($lang) ? array_shift($lang) : $lang);

        // fix if lang is -1
        if ($this->lang < 0) {
            $this->lang = 0;
        }

        $this->typeNum = $typeNum;

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $fullTS = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->config = $fullTS['config.'] ?? [];
    }

    public function getPagePath(): string
    {
        $parameter = [
            'parameter' => $this->pageUid,
            'additionalParams' => '&L=' . (int)$this->lang,
            'forceAbsoluteUrl' => 1,
        ];

        return $this->cObj->typoLink_URL($parameter);
    }

    public function getPage(): array
    {
        return $GLOBALS['TSFE']->page;
    }

    public function getPageTitleFirst(): bool
    {
        return isset($this->config['pageTitleFirst']) ? (bool)$this->config['pageTitleFirst'] : false;
    }

    public function getFinalTitle(string $title, bool $titleOnly = false): string
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
        $siteTitle = '';
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            $site = $siteFinder->getSiteByPageId($this->pageUid);
            $siteTitle = $site->getConfiguration()['websiteTitle'] ?? '';
            if ($this->lang > 0) {
                try {
                    $siteLangauge = $site->getLanguageById($this->lang);
                    if ($siteLangauge && !empty($siteLangauge->getWebsiteTitle())) {
                        $siteTitle = $siteLangauge->getWebsiteTitle();
                    }
                } catch (\InvalidArgumentException) {
                }
            }
        } catch (SiteNotFoundException $exception) {
        }

        return $siteTitle;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getPageTitleSeparator(): string
    {
        return $this->cObj->stdWrap(
            $this->config['pageTitleSeparator'] ?? '',
            $this->config['pageTitleSeparator.'] ?? []
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
