<?php
namespace Clickstorm\CsSeo\HrefLang\EventListener;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Service\MetaDataService;

class CsSeoHrefLang
{
	public function __invoke(ModifyHrefLangTagsEvent $event): void
	{
		$xdefault = ConfigurationUtility::getXdefault();
		$metaDataService = GeneralUtility::makeInstance(MetaDataService::class);
		$metaData = $metaDataService->getMetaData();

		$siteLanguages = $event->getRequest()->getAttributes()['site']->getConfiguration()['languages'];
		$hrefLangs = $event->getHrefLangs();

		if($xdefault > 0) {
			foreach ($siteLanguages as $language) {
				if($language['languageId'] == $xdefault) {
					$xdefaultLang = $language;
				}
			}
		}

		// set xdefault language. If its empty just use the default already set by typo3
		if($hrefLangs[$xdefaultLang['hreflang']]) {
			$hrefLangs['x-default'] = $hrefLangs[$xdefaultLang['hreflang']];
		}
		$event->setHrefLangs($hrefLangs);
	}
}