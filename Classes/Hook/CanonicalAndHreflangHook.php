<?php

namespace Clickstorm\CsSeo\Hook;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor;
use TYPO3\CMS\Seo\Canonical\CanonicalGenerator;
use TYPO3\CMS\Seo\HrefLang\HrefLangGenerator;

/**
 * Class to add the canonical tag to the page
 *
 * @internal this class is not part of TYPO3's Core API.
 */
class CanonicalAndHreflangHook
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * constructor
     *
     * @param TypoScriptFrontendController $typoScriptFrontendController
     * @param Dispatcher $signalSlotDispatcher
     */
    public function __construct(
        TypoScriptFrontendController $typoScriptFrontendController = null,
        Dispatcher $signalSlotDispatcher = null
    ) {
        if ($typoScriptFrontendController === null) {
            $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        }
        if ($signalSlotDispatcher === null) {
            $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        }
        $this->typoScriptFrontendController = $typoScriptFrontendController;
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function generate(): void
    {
        $href = '';
        $this->signalSlotDispatcher->dispatch(self::class, 'beforeGeneratingCanonical', [&$href]);

        $hreflangs = '';
        $this->signalSlotDispatcher->dispatch(self::class, 'beforeGeneratingHreflang', [&$hreflangs]);

        // stop here if both should not be set
        if ($href === 'none' && $hreflangs === 'none') {
            return;
        }

        if (empty($href) || empty($hreflangs)) {
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
                $currentLanguageUid = $context->getAspect('language')->getId();

                $tables = ConfigurationUtility::getPageTSconfig();
                $currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);

                $l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], $currentItemConf['uid']);

                unset($typoLinkConf['parameter.']);
                $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

                if (empty($href) && !$metaData['no_index']) {
                    // canonical
                    $canonicalTypoLinkConf = ['forceAbsoluteUrl' => 1];
                    if ($metaData['canonical']) {
                        $canonicalTypoLinkConf['parameter'] = $metaData['canonical'];
                    } else {
                        $canonicalTypoLinkConf = $typoLinkConf;

                        // if a fallback is shown, set canonical to the language of the ordered item
                        if (!in_array($currentLanguageUid, $l10nItems)) {
                            unset($canonicalTypoLinkConf['additionalParams.']['append.']['data']);
                            $lang = $this->getLanguageFromItem($currentItemConf['table'], $currentItemConf['uid']);
                            if ($lang < 0) {
                                $lang = 0;
                            }
                            $canonicalTypoLinkConf['additionalParams.']['append.']['value'] = $lang;
                        }
                    }
                    $href = $cObj->typoLink_URL($canonicalTypoLinkConf);
                }

                if (empty($hreflangs)
                    && in_array($currentLanguageUid, $l10nItems)
                    && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
                    && $href === GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')
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
                            $hrefLangArray[] = ['hreflang' => $language['hreflang'], 'href' => $hreflangUrl];
                        }
                    }
                    $hreflangs = $this->printHreflangs($hrefLangArray);
                }

                // pages record
            } else {
                // use own implementation for canonicals and hreflangs
                if ($useAdditionalCanonicalizedUrlParametersOnly) {
                    if (empty($GLOBALS['TSFE']->typoScriptFrontendController->page['no_index'])) {
                        if (empty($href)) {
                            $href = $cObj->typoLink_URL($typoLinkConf);
                        }

                        if (empty($hreflangs)
                            && empty($GLOBALS['TSFE']->typoScriptFrontendController->page['content_from_pid'])
                            && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {

                            $hrefLangArray = [];
                            $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                            $languages = $languageMenu->process($cObj, [], [], []);

                            // prepate typolink conf for dynamic hreflang
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
                                    if(empty($canonicalsByLanguages[$language['languageId']])) {
                                        $hreflangTypoLinkConf['language'] = $language['languageId'];
                                        $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);
                                    } else {
                                        $hreflangTypoLinkConfForCanonical['parameter'] = $canonicalsByLanguages[$language['languageId']];
                                        $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConfForCanonical);
                                    }
                                    $hrefLangArray[] = ['hreflang' => $language['hreflang'], 'href' => $hreflangUrl];
                                }
                            }
                            
                            $hreflangs = $this->printHreflangs($hrefLangArray);
                        }
                    }
                    // use core implementation for canonicals and hreflangs
                } else {
                    $GLOBALS['TSFE']->linkVars = $tempLinkVars;
                    $canonicalGenerator = GeneralUtility::makeInstance(CanonicalGenerator::class);
                    $canonicalGenerator->generate();
                    $hrefLangGenerator = GeneralUtility::makeInstance(HrefLangGenerator::class);
                    $hrefLangGenerator->generate();

                    return;
                }
            }
            if ($useAdditionalCanonicalizedUrlParametersOnly) {
                $GLOBALS['TSFE']->linkVars = $tempLinkVars;
            }
        }
        $this->printCanonical($href);
        $this->addHreflangsToHeaderData($hreflangs);
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return array
     */
    protected function getAllLanguagesFromItem($table, $uid)
    {
        $languageIds = [];
        if (!isset($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']) || !isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])) {
            return $languageIds;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $pointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        $allItems = $queryBuilder->select($languageField)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    $pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->orWhere(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        foreach ($allItems as $item) {
            $languageIds[] = $item[$languageField];
        }

        return $languageIds;
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return int
     */
    protected function getLanguageFromItem($table, $uid)
    {
        if ($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetchAll();

            return $items[0]['sys_language_uid'];
        }

        return 0;
    }

    /**
     * converts hreflang links to HTML tags
     *
     * @param array $hrefLangArray
     * @return string
     */
    protected function printHreflangs($hrefLangArray)
    {
        $hreflangs = '';
        // add the x-default
        if (count($hrefLangArray) > 0) {
            $hrefLangArray[] = ['hreflang' => 'x-default', 'href' => $hrefLangArray[0]['href']];
            foreach ($hrefLangArray as $item) {
                $hreflangs .= '<link rel="alternate" hreflang="' . $item['hreflang'] . '" href="' . $item['href'] . '" />';
            }
        }

        return $hreflangs;
    }

    /**
     * @param string $uid
     *
     * @return int
     */
    protected function getCanonicalFromAllLanguagesOfPage($uid)
    {
        $res = [];

        $table = 'pages';
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'], 'canonical_link')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->orWhere(
                $queryBuilder->expr()->eq(
                    $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'],
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        foreach ($items as $item) {
            $res[$item['sys_language_uid']] = $item['canonical_link'];
        }

        return $res;
    }

    /**
     * convert the link to an canonical tag and add them to the header data
     *
     * @param string $href
     */
    protected function printCanonical($href)
    {
        if (!empty($href) && $href !== 'none') {
            $canonical = '<link ' . GeneralUtility::implodeAttributes([
                    'rel' => 'canonical',
                    'href' => $href
                ], true) . '/>' . LF;
            $this->typoScriptFrontendController->additionalHeaderData[] = $canonical;
        }
    }

    /**
     * finally add the hreflang HTML to additional header data
     *
     * @param string $hreflangs
     */
    protected function addHreflangsToHeaderData($hreflangs)
    {
        if (!empty($hreflangs) && $hreflangs !== 'none') {
            $this->typoScriptFrontendController->additionalHeaderData[] = $hreflangs;
        }
    }
}
