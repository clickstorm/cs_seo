<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Controller\ModuleWebController;
use TYPO3\CMS\Backend\Module\ExtbaseModule;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Policy;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;

#[AsEventListener(
    identifier: 'cs-seo/mutate-policy',
)]
final readonly class PolicyMutatedEventListener
{
    public function __invoke(PolicyMutatedEvent $event): void
    {
        // only for backend CSP
        // @extensionScannerIgnoreLine
        if ($event->scope->type->isFrontend()) {
            return;
        }

        /** @var ?ExtbaseModule $module */
        $module = $event->request?->getAttribute('module');

        // only for cs_seo web module
        if ($module instanceof ExtbaseModule && str_starts_with($module->getIdentifier(), ModuleWebController::$mod_name)) {

            // overwrite the policy so the script and style from the JS module can be used
            $policy = (new Policy())
                ->default(SourceKeyword::self)
                ->extend(Directive::ScriptSrc, SourceKeyword::unsafeInline)
                ->extend(Directive::StyleSrc, SourceKeyword::unsafeInline)
                ->extend(Directive::ImgSrc, SourceScheme::data);
            $event->setCurrentPolicy($policy);
        }
    }
}
