<?php

declare(strict_types=1);

use Clickstorm\CsSeo\Command\EvaluationCommand;
use Clickstorm\CsSeo\Form\Element\JsonLdElement;
use Clickstorm\CsSeo\Form\Element\SnippetPreview;
use Clickstorm\CsSeo\Hook\MetaTagGeneratorHook;
use Clickstorm\CsSeo\UserFunc\StructuredData;
use Clickstorm\CsSeo\UserFunc\Tca;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Clickstorm\CsSeo\\', __DIR__ . '/../Classes/*');

    $services->load('Clickstorm\CsSeo\Service\\', __DIR__ . '/../Classes/Service/*')
        ->public();

    $services->set(MetaTagGeneratorHook::class)
        ->public();

    $services->set(SnippetPreview::class)
        ->public();

    $services->set(JsonLdElement::class)
        ->public();

    $services->set(EvaluationCommand::class)
        ->public();

    $services->set(Tca::class)
        ->public();

    $services->set(StructuredData::class)
        ->public();
};
