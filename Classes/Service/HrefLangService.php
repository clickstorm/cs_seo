<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor;

/**
 * Class to use own implementation of hreflang tags
 */
class HrefLangService extends AbstractUrlService
{
    /**
     * @param array $hreflangs
     *
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function getHrefLangs($hreflangs): array
    {
        $metaDataService = GeneralUtility::makeInstance(MetaDataService::class);
        $metaData = $metaDataService->getMetaData();
        $useAdditionalCanonicalizedUrlParametersOnly = ConfigurationUtility::useAdditionalCanonicalizedUrlParametersOnly();

        /** @var ContentObjectRenderer $cObj */
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $typoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
        $tempLinkVars = $GLOBALS['TSFE']->linkVars;

        // remove config.linkVars temporary
        if ($useAdditionalCanonicalizedUrlParametersOnly) {
            $GLOBALS['TSFE']->linkVars = '';
        }

        // check if the current page is a detail page of a record
        if ($metaData) {
            $hrefLangArray = [];
            $currentLanguageUid = $context->getAspect('language')->getId();

            $tables = ConfigurationUtility::getPageTSconfig();
            $currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);

            $l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], $currentItemConf['uid']);

            unset($typoLinkConf['parameter.']);
            $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

            if (empty($metaData['no_index']) &&
                empty($metaData['canonical']) &&
                in_array($currentLanguageUid, $l10nItems) &&
                $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
            ) {
                $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                $languages = $languageMenu->process($cObj, [], [], []);
                $hreflangTypoLinkConf = $typoLinkConf;
                unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);

                foreach ($languages['languagemenu'] as $language) {
                    // set hreflang only for languages of the TS setup and if the language is also localized for the item
                    // if the language doesn't exist for the item and a fallback language is shown, the hreflang is not set and the canonical points to the fallback url
                    if ($language['available'] === 1 && in_array($language['languageId'], $l10nItems)) {
                        $hreflangTypoLinkConf['language'] = $language['languageId'];
                        $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);
                        $hrefLangArray[$language['languageId']] = [
                            'hreflang' => $language['hreflang'],
                            'href' => $hreflangUrl
                        ];
                    }
                }
                $hreflangs = $this->finalizeHrefLangs($hrefLangArray);
            }

            // pages record
        } else {
            // use own implementation for canonicals and hreflangs by config or if x-default equals not the default language
            // @TODO: own implementation can be removed, when https://forge.typo3.org/issues/90936 is fixed
            if ($useAdditionalCanonicalizedUrlParametersOnly || ConfigurationUtility::getXdefault() > 0) {
                $hrefLangArray = [];
                if (empty($GLOBALS['TSFE']->typoScriptFrontendController->page['no_index'])
                    && empty($GLOBALS['TSFE']->typoScriptFrontendController->page['content_from_pid'])
                    && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {
                    $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                    $languages = $languageMenu->process($cObj, [], [], []);

                    // prepare typolink conf for dynamic hreflang
                    $hreflangTypoLinkConf = $typoLinkConf;
                    unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);
                    unset($hreflangTypoLinkConf['parameter.']);
                    $hreflangTypoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

                    // prepare typolink conf for hreflang with canonical link
                    $hreflangTypoLinkConfForCanonical = $hreflangTypoLinkConf;
                    unset($hreflangTypoLinkConfForCanonical['additionalParams.']);

                    $canonicalsByLanguages = $this->getCanonicalFromAllLanguagesOfPage($GLOBALS['TSFE']->id);

                    foreach ($languages['languagemenu'] as $language) {
                        if ($language['available'] === 1 && !empty($language['link'])) {
                            // check canonicals from all languages
                            if (empty($canonicalsByLanguages[$language['languageId']])) {
                                $hreflangTypoLinkConf['language'] = $language['languageId'];
                                $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);
                            } else {
                                $hreflangTypoLinkConfForCanonical['parameter'] = $canonicalsByLanguages[$language['languageId']];
                                $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConfForCanonical);
                            }
                            $hrefLangArray[$language['languageId']] = [
                                'hreflang' => $language['hreflang'],
                                'href' => $hreflangUrl
                            ];
                            $hreflangs = $this->finalizeHrefLangs($hrefLangArray);
                        }
                    }
                }
            }
        }

        $GLOBALS['TSFE']->linkVars = $tempLinkVars;

        return $hreflangs;
    }

    /**
     * add the x-default parameter and convert to final hrefLang
     *
     * @param array $hrefLangArray
     * @return array
     */
    protected
    function finalizeHrefLangs(
        $hrefLangArray
    ) {
        $hreflangs = [];
        // add the x-default
        if (count($hrefLangArray) > 0) {
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
