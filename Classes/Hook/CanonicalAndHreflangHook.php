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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $metaDataService = GeneralUtility::makeInstance(MetaDataService::class);

        $metaData = $metaDataService->getMetaData();

        if ($metaData) {
            /** @var ContentObjectRenderer $cObj */
            $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $context = GeneralUtility::makeInstance(Context::class);

            $currentLanguageUid = $context->getAspect('language')->getId();

            $tables = ConfigurationUtility::getPageTSconfig();
            $currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);

            $l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], $currentItemConf['uid']);

            $typoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
            unset($typoLinkConf['parameter.']);
            $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

            $href = '';
            $this->signalSlotDispatcher->dispatch(self::class, 'beforeGeneratingCanonical', [&$href]);
            if ($href !== 'none') {
                if (empty($href)) {


                    // canonical
                    $canonicalTypoLinkConf = [];
                    if ($metaData['canonical']) {
                        $canonicalTypoLinkConf['parameter'] = $metaData['canonical'];
                        $canonicalTypoLinkConf['forceAbsoluteUrl'] = 1;
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
                    if (!$metaData['no_index']) {
                        $href = $cObj->typoLink_URL($canonicalTypoLinkConf);
                    }
                }

                if (!empty($href)) {
                    $canonical = '<link ' . GeneralUtility::implodeAttributes([
                            'rel' => 'canonical',
                            'href' => $href
                        ], true) . '/>' . LF;
                    $this->typoScriptFrontendController->additionalHeaderData[] = $canonical;
                }
            }

            $hreflangs = '';
            $this->signalSlotDispatcher->dispatch(self::class, 'beforeGeneratingHreflang', [&$hreflangs]);

            if ($hreflangs !== 'none') {
                if (empty($hreflangs)) {
                    // hreflang
                    // if the item for the current language uid exists and
                    // the item is not set to no index and
                    // the item points not to another page as canonical and
                    // the TS setting hreflang.enabled is set to 1
                    if (in_array(
                            $currentLanguageUid,
                            $l10nItems
                        )
                        && !$metaData['no_index']
                        && !$metaData['canonical']
                        && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
                    ) {
                        $languageMenu = GeneralUtility::makeInstance(LanguageMenuProcessor::class);
                        $languages = $languageMenu->process($cObj, [], [], []);
                        $hreflangTypoLinkConf = $typoLinkConf;
                        $metaTags['hreflang'] = '';

                        foreach ($languages['languagemenu'] as $language) {
                            // set hreflang only for languages of the TS setup and if the language is also localized for the item
                            // if the language doesn't exist for the item and a fallback language is shown, the hreflang is not set and the canonical points to the fallback url
                            if ($language['available'] === 1 && in_array($language['1anguageId'], $l10nItems)) {
                                unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);
                                $hreflangTypoLinkConf['additionalParams.']['append.']['value'] = $language['1anguageId'];
                                $hreflangUrl = $cObj->typoLink_URL($hreflangTypoLinkConf);
                                $hreflangs .= '<link rel="alternate" hreflang="'
                                    . $language['hreflang']
                                    . '" href="'
                                    . $hreflangUrl
                                    . '" />';
                            }
                        }
                    }
                }
                $this->typoScriptFrontendController->additionalHeaderData[] = $hreflangs;
            }

            // if no extension metadata use the core href lang generator
        } else {
            $canonicalGenerator = GeneralUtility::makeInstance(CanonicalGenerator::class);
            $canonicalGenerator->generate();
            $hrefLangGenerator = GeneralUtility::makeInstance(HrefLangGenerator::class);
            $hrefLangGenerator->generate();
        }

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
                $queryBuilder->expr()->eq($pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->orWhere(
                $queryBuilder->expr()->eq('uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();

        foreach ($allItems as $item) {
            $languageIds[] = $item[$languageField];
        }

        return $languageIds;
    }
}
