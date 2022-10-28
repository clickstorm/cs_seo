<?php

use Clickstorm\CsSeo\Controller\ModuleWebController;
use Clickstorm\CsSeo\Controller\ModuleFileController;

return [
    'web_CsSeoMod1' => [
        'parent' => 'web',
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-web',
        'path' => '/module/web/cs-seo',
        'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/web.xlf',
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleWebController::class =>
                'pageMeta, pageIndex, pageOpenGraph, pageTwitterCards, pageStructuredData, pageResults, pageEvaluation',
        ],
    ],
    'file_CsSeoModFile' => [
        'parent' => 'file',
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-file',
        'path' => '/module/file/cs-seo',
        'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/Module/web.xlf',
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleFileController::class => 'showEmptyImageAlt,update',
        ],
    ],
];