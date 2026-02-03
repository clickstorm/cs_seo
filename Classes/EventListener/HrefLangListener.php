<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Service\HrefLangService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;

/**
 * Override core hrefLangs to prevent parameters in URL and use metadata from records
 */
#[AsEventListener(
    identifier: 'cs-seo/hreflang',
    after: 'typo3-seo/hreflangGenerator'
)]
class HrefLangListener
{
    public function __invoke(ModifyHrefLangTagsEvent $event): void
    {
        $hrefLangService = GeneralUtility::makeInstance(HrefLangService::class);
        $event->setHrefLangs($hrefLangService->getHrefLangs($event->getHrefLangs()));
    }
}
