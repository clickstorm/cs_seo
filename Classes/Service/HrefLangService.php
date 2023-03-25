<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor;

/**
 * Class to use own implementation of hreflang tags
 */
class HrefLangService extends AbstractUrlService
{
    /**
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws AspectNotFoundException
     */
    public function getHrefLangs(array $hreflangs): array
    {
        $metaDataService = GeneralUtility::makeInstance(MetaDataService::class);
        $metaData = $metaDataService->getMetaData();

        /** @var ContentObjectRenderer $cObj */
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $typoLinkConf = isset($GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.']);
        $tempLinkVars = $GLOBALS['TSFE']->linkVars;

        // remove config.linkVars temporary
        $GLOBALS['TSFE']->linkVars = '';

        // check if the current page is a detail page of a record
        if ($metaData) {
            $hrefLangArray = [];
            $currentLanguageUid = $context->getAspect('language')->getId();
            $tables = ConfigurationUtility::getTablesToExtend();
            $currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);
            $l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], (int)$currentItemConf['uid']);
            unset($typoLinkConf['parameter.']);
            // @extensionScannerIgnoreLine
            $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;
            if (empty($metaData['no_index']) &&
                empty($metaData['canonical']) &&
                isset($l10nItems[$currentLanguageUid]) &&
                $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
            ) {
                $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                $languages = $languageMenu->process($cObj, [], [], []);
                $hreflangTypoLinkConf = $typoLinkConf;
                unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);

                foreach ($languages['languagemenu'] as $language) {
                    // set hreflang only for languages of the TS setup and if the language is also localized for the item
                    // if the language doesn't exist for the item and a fallback language is shown, the hreflang is not set and the canonical points to the fallback url
                    if ($this->checkHrefLangForLanguageCanBeSet(
                            $language,
                            $languages['languagemenu']
                        ) && isset($l10nItems[$language['languageId']])) {
                        $hreflangTypoLinkConf['language'] = $language['languageId'];
                        $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);
                        $hrefLangArray[$language['languageId']] = [
                            'hreflang' => $language['hreflang'],
                            'href' => $hreflangUrl,
                        ];
                    }
                }
                $hreflangs = $this->finalizeHrefLangs($hrefLangArray);
            } else {
                // remove hreflangs, if item is set to no_index or has a different canonical
                $hreflangs = [];
            }
            // pages record
        } elseif (ConfigurationUtility::getXdefault() > 0) {
            // remove hreflangs
            $hreflangs = [];
            $hrefLangArray = [];
            if (empty($GLOBALS['TSFE']->page['no_index'])
                && empty($GLOBALS['TSFE']->page['canonical_link'])
                && empty($GLOBALS['TSFE']->page['content_from_pid'])
                && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {
                $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                $languages = $languageMenu->process($cObj, [], [], []);

                // prepare typolink conf for dynamic hreflang
                $hreflangTypoLinkConf = $typoLinkConf;
                unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);
                unset($hreflangTypoLinkConf['parameter.']);
                // @extensionScannerIgnoreLine
                $hreflangTypoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

                // prepare typolink conf for hreflang with canonical link
                $hreflangTypoLinkConfForCanonical = $hreflangTypoLinkConf;
                unset($hreflangTypoLinkConfForCanonical['additionalParams.']);

                // @extensionScannerIgnoreLine
                $canonicalsByLanguages = $this->getCanonicalFromAllLanguagesOfPage($GLOBALS['TSFE']->id);

                foreach ($languages['languagemenu'] as $language) {
                    if ($this->checkHrefLangForLanguageCanBeSet($language, $languages['languagemenu'])
                        && empty($canonicalsByLanguages[$language['languageId']])) {
                        $hreflangTypoLinkConf['language'] = $language['languageId'];
                        $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);

                        $hrefLangArray[$language['languageId']] = [
                            'hreflang' => $language['hreflang'],
                            'href' => $hreflangUrl,
                        ];
                        $hreflangs = $this->finalizeHrefLangs($hrefLangArray);
                    }
                }
            }
        }

        $GLOBALS['TSFE']->linkVars = $tempLinkVars;

        return $hreflangs;
    }

    /**
     * check if a hreflang for the given language of the languageMenu can be set
     */
    protected function checkHrefLangForLanguageCanBeSet(array $language, array $languageMenu): bool
    {
        if (!empty($language['hreflang']) && !empty($language['link'])) {
            if ($language['available']) {
                return true;
            }

            // if not already defined, get the site languages
            if (empty($this->siteLanguages) && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {
                $this->siteLanguages = $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getLanguages();
            }

            // if language not available, so no translation given, check for fallback
            // needed until https://forge.typo3.org/issues/94207 is solved
            /** @var SiteLanguage $currentSiteLanguage */
            $currentSiteLanguage = $this->siteLanguages[$language['languageId']];

            if ($currentSiteLanguage instanceof SiteLanguage && $currentSiteLanguage->getFallbackType() === 'fallback' && $currentSiteLanguage->getFallbackLanguageIds()) {
                foreach ($currentSiteLanguage->getFallbackLanguageIds() as $fallbackLanguageId) {
                    foreach ($languageMenu as $languageMenuLanguage) {
                        if ($languageMenuLanguage['languageId'] === $fallbackLanguageId) {
                            return $this->checkHrefLangForLanguageCanBeSet($languageMenuLanguage, $languageMenu);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * add the x-default parameter and convert to final hrefLang
     */
    protected function finalizeHrefLangs(array $hrefLangArray): array
    {
        $hreflangs = [];
        // add the x-default
        if (count($hrefLangArray) > 1) {
            $xDefaultLanguageId = ConfigurationUtility::getXdefault();
            if (isset($hrefLangArray[$xDefaultLanguageId]) && $hrefLangArray[$xDefaultLanguageId]['href']) {
                $hreflangs['x-default'] = $hrefLangArray[$xDefaultLanguageId]['href'];
            }
            foreach ($hrefLangArray as $item) {
                $hreflangs[$item['hreflang']] = $item['href'];
            }
        }

        return $hreflangs;
    }
}
