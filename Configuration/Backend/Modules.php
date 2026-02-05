<?php

use Clickstorm\CsSeo\Controller\ModuleMediaController;
use Clickstorm\CsSeo\Controller\ModuleContentController;

$csSeoModules =  [
    'content_csseo' => [
        'parent' => 'content',
        'showSubmoduleOverview' => true,
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-content',
        'path' => '/module/content/cs-seo',
        'labels' => 'cs_seo.modules.content',
        'extensionName' => 'CsSeo',
        'moduleData' => [
            'pages' => '0',
            'depth' => 0,
            'lang' => 0,
        ],
    ],
    'media_csseo' => [
        'parent' => 'media',
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-media',
        'path' => '/module/media/cs-seo',
        'labels' =>'cs_seo.modules.media',
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleMediaController::class => 'showEmptyImageAlt,update',
        ],
        'moduleData' => [
            'id' => '0',
        ],
    ],
];

foreach (ModuleContentController::$menuActions as $key => $action) {
    $csSeoModules['content_csseo_' . $key] = [
        'parent' => 'content_csseo',
        'position' => ['before' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-' . $key,
        'path' => '/module/content/cs-seo/' . strtolower((string)preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', (string)$action)),
        'labels' => 'cs_seo.modules.content.' . $key,
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleContentController::class => [
                $action,
            ],
        ],
        'moduleData' => [
            'pages' => '0',
            'depth' => 0,
            'lang' => 0,
        ],
    ];
}

return $csSeoModules;
