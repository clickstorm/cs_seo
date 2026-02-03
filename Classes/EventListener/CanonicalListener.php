<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Service\CanonicalService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Seo\Event\ModifyUrlForCanonicalTagEvent;

/**
 * Listen To the ModifyUrlForCanonicalTagEvent and modify the canonical if necessary
 */
#[AsEventListener(
    identifier: 'cs-seo/canonical',
)]
class CanonicalListener
{
    public function __invoke(ModifyUrlForCanonicalTagEvent $event): void
    {
        $canonicalService = GeneralUtility::makeInstance(CanonicalService::class);
        // @extensionScannerIgnoreLine
        $event->setUrl($canonicalService->getUrl($event->getUrl()));
    }
}
