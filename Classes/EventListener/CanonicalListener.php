<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Service\CanonicalService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Seo\Event\ModifyUrlForCanonicalTagEvent;

/**
 * Listen To the ModifyUrlForCanonicalTagEvent and modify the canonical if necessary
 */
class CanonicalListener
{
    public function __invoke(ModifyUrlForCanonicalTagEvent $event): void
    {
        if (empty($event->getUrl())) {
            $canonicalService = GeneralUtility::makeInstance(CanonicalService::class);
            $event->setUrl($canonicalService->getUrl());
        }
    }
}
