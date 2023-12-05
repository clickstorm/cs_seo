<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Modify the canonical to respect information from the record and prevent dynamic URL parameters
 */
class CanonicalService extends AbstractUrlService
{
    /**
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws AspectNotFoundException
     */
    public function getUrl(): string
    {
        $canonicalUrl = '';

        $metaDataService = GeneralUtility::makeInstance(MetaDataService::class);
        $metaData = $metaDataService->getMetaData();

        /** @var ContentObjectRenderer $cObj */
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $typoLinkConf = $this->typoScriptFrontendController->tmpl->setup['lib.']['currentUrl.']['typolink.'] ?? [];
        $tempLinkVars = $this->typoScriptFrontendController->linkVars;

        // remove config.linkVars temporary
        $GLOBALS['TSFE']->linkVars = '';

        // check if the current page is a detail page of a record
        if ($metaData) {
            $currentLanguageUid = $context->getAspect('language')->getId();
            $tables = ConfigurationUtility::getTablesToExtend();
            $currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);
            $l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], (int)$currentItemConf['uid']);
            unset($typoLinkConf['parameter.']);
            // @extensionScannerIgnoreLine
            $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;
            if (!empty($metaData['no_index'])) {
                $this->typoScriptFrontendController->page['no_index'] = 1;
            } else {
                // canonical
                $canonicalTypoLinkConf = ['forceAbsoluteUrl' => 1];
                if (!empty($metaData['canonical'])) {
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
                        $canonicalTypoLinkConf['language'] = $lang;
                    }
                }
                $canonicalUrl = $cObj->typoLink_URL($canonicalTypoLinkConf);
            }
        // pages record
        } elseif (empty($this->typoScriptFrontendController->page['no_index']) &&
            empty($this->typoScriptFrontendController->page['canonical_link']) &&
            empty($this->typoScriptFrontendController->page['content_from_pid'])) {
            $canonicalUrl = $cObj->typoLink_URL($typoLinkConf);
        }

        $GLOBALS['TSFE']->linkVars = $tempLinkVars;

        return $canonicalUrl;
    }
}
