<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\Event\AfterGetDataResolvedEvent;

#[AsEventListener(
    identifier: 'cs-seo/after-get-data-resolved',
)]
class AfterGetDataResolvedEventListener
{
    public function __invoke(AfterGetDataResolvedEvent $event): void
    {
        $getDataString = $event->getParameterString();
        if ($getDataString === 'tx_csseo_url_parameters') {
            $request = GlobalsUtility::getTYPO3Request();
            $pageArguments = $request->getAttribute('routing');
            if ($pageArguments instanceof PageArguments) {
                $currentQueryArray = $pageArguments->getRouteArguments();

                $event->setResult(GeneralUtility::implodeArrayForUrl('', $currentQueryArray));
            }
        }
    }
}
