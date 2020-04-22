<?php
namespace Clickstorm\CsSeo\HrefLang\EventListener;

use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;

class CsSeoHrefLang
{
	public function __invoke(ModifyHrefLangTagsEvent $event): void
	{
		$hrefLangs = $event->getHrefLangs();
		$request = $event->getRequest();

		// Do anything you want with $hrefLangs
		$hrefLangs = [
			'en-US' => 'https://example.com',
			'nl-NL' => 'https://example.com/nl'
		];

		// Override all hrefLang tags
		//$event->setHrefLangs($hrefLangs);

		// Or add a single hrefLang tag
		$event->addHrefLang('de-DE', 'https://example.com/de');
	}
}