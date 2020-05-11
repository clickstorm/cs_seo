<?php
namespace Clickstorm\CsSeo\HrefLang\EventListener;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;
use TYPO3\CMS\Core\Context\Context;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Hook\CanonicalAndHreflangHook;
use TYPO3\CMS\Frontend\Page\PageRepository;

class CsSeoHrefLang extends CanonicalAndHreflangHook
{
	public function __invoke(ModifyHrefLangTagsEvent $event): void
	{
		$xdefault = ConfigurationUtility::getXdefault();
		$metaDataService = GeneralUtility::makeInstance(MetaDataService::class);
		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		$context = GeneralUtility::makeInstance(Context::class);
		$metaData = $metaDataService->getMetaData();

		$siteLanguages = $event->getRequest()->getAttributes()['site']->getConfiguration()['languages'];
		$currentLanguage = $event->getRequest()->getAttributes()['language'];
		$currentPageId = $event->getRequest()->getAttributes()['routing']->getPageId();
		// The language with that the content will get overlayed
		$overlayedSysLanguageUid = $pageRepository->getPageOverlay($currentPageId)['sys_language_uid'];

		// Get the language aspects from context
		$requestedLanguageId = $context->getPropertyFromAspect('language', 'id');
		$fallbackChain = $context->getPropertyFromAspect('language', 'fallbackChain');
		$overlayType = $context->getPropertyFromAspect('language', 'overlayType');

		$hrefLangs = $event->getHrefLangs();

		// get the $xdefaultLang
		if($xdefault > 0) {
			foreach ($siteLanguages as $language) {
				if($language['languageId'] == $xdefault) {
					$xdefaultLang = $language;
				}
			}
		}

		// detail pages
		if($metaData) {
			$cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
			$tables = ConfigurationUtility::getPageTSconfig();
			$hreflangTypoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
			$currentItemConf = $metaDataService::getCurrentTableConfiguration($tables, $cObj);
			$l10nItems = $this->getAllLanguagesFromItem($currentItemConf['table'], $currentItemConf['uid']);

			foreach ($siteLanguages as $language) {
				// set hreflang only for languages of the TS setup and if the language is also localized for the item
				if ($language['enabled'] === false || !in_array($language['languageId'], $l10nItems)) {
					unset($hrefLangs[$language['hreflang']]);
				}
			}

			// Language fallback for item, just use default language as canonical and dont show hreflang
			// There is no language fallback ordering for content elements, just for pages.
			// If a fallback is shown, it will always be in the default language 0
			if(!in_array($requestedLanguageId, $l10nItems)) {
				// default language got overlayed, since there is no fallback ordering for content elements in typo3 10
				$canonicalUrl = $hrefLangs[$siteLanguages[0]['hreflang']];
				// clear and set new canonical
				$this->typoScriptFrontendController->additionalHeaderData = [];
				$this->printCanonical($canonicalUrl);
				$hrefLangs = [];
			}
		}

		// check for language fallback
		// if the language doesn't exist for the item and a fallback language is shown,
		// the hreflang is not set and the canonical points to the fallback url
		// If the page is disabled or not translated, the contentfallback ordering will be applied
		// If overlayedSysLanguageUid is Null, the default page language got overlayed
		if($currentLanguage->getFallbackType() != 'strict') {
			// Current page has active language fallback
			if($overlayedSysLanguageUid != $requestedLanguageId) {
				foreach ($siteLanguages as $language) {
					if($language['languageId'] == $overlayedSysLanguageUid) {
						$canonicalUrl = $hrefLangs[$language['hreflang']];
						break;
					}
				}
				// default language overlayed
				if($overlayedSysLanguageUid == NULL) {
					$canonicalUrl = $hrefLangs[$siteLanguages[0]['hreflang']];
				}
				// clear and set new canonical
				$this->typoScriptFrontendController->additionalHeaderData = [];
				$this->printCanonical($canonicalUrl);
				// dont use hrefLangs
				$hrefLangs = [];
			}
		}

		// set the xdefault language.
		// If its empty, the page is not translated into the x-default lang, so unset x-default if its not the default language
		if($hrefLangs[$xdefaultLang['hreflang']]) {
			$hrefLangs['x-default'] = $hrefLangs[$xdefaultLang['hreflang']];
		}
		elseif ($xdefault != 0) {
			unset($hrefLangs['x-default']);
		}
		// update hrefLangs
		$event->setHrefLangs($hrefLangs);
	}
}