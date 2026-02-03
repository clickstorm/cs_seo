<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * own Service to get TypoScript config in backend
 */
class FrontendConfigurationService
{
    protected int $pageUid = 0;

    protected int $workspaceUid = 0;

    protected int $parentUid = 0;

    protected int $lang = 0;

    protected array $typoScriptConfig = [];

    protected ?ContentObjectRenderer $cObj = null;

    /**
     * @throws Exception
     */
    public function __construct(int $pageUid = 0, int $lang = 0, protected int $typeNum = 654)
    {
        if ($pageUid === 0) {
            $pageUid = GlobalsUtility::getPageId();
        }
        $this->pageUid = $pageUid;

        $this->workspaceUid = GlobalsUtility::getBackendUser()->workspace ?? 0;
        $this->lang = (int)(is_array($lang) ? array_shift($lang) : $lang);

        // fix if lang is -1
        if ($this->lang < 0) {
            $this->lang = 0;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $fullTS = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->typoScriptConfig = $fullTS['config.'] ?? [];
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

    public function getPageTitleFirst(): bool
    {
        return isset($this->typoScriptConfig['pageTitleFirst']) && (bool)$this->typoScriptConfig['pageTitleFirst'];
    }

    public function getFinalTitle(string $title, bool $titleOnly = false): string
    {
        if ($titleOnly) {
            return $title;
        }

        $siteTitle = $this->getSiteTitle();
        $pageTitleFirst = $this->getTypoScriptConfig()['pageTitleFirst'];
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
            try {
                $siteLanguage = $site->getLanguageById($this->lang);
                if ($siteLanguage && !empty($siteLanguage->getWebsiteTitle())) {
                    $siteTitle = $siteLanguage->getWebsiteTitle();
                }
            } catch (\InvalidArgumentException) {
            }
        } catch (SiteNotFoundException) {
        }

        return $siteTitle;
    }

    public function getTypoScriptConfig(): array
    {
        return $this->typoScriptConfig;
    }

    public function getPageTitleSeparator(): string
    {
        return $this->cObj->stdWrap(
            $this->typoScriptConfig['pageTitleSeparator'] ?? '',
            $this->typoScriptConfig['pageTitleSeparator.'] ?? []
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
        return (GlobalsUtility::getTYPO3Request() ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest(GlobalsUtility::getTYPO3Request())->isBackend();
    }
}
