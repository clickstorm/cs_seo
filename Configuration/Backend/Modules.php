<?php

use Clickstorm\CsSeo\Controller\ModuleFileController;
use Clickstorm\CsSeo\Controller\ModuleWebController;

$csSeoModules =  [
    'web_CsSeoMod1' => [
        'parent' => 'content',
        'showSubmoduleOverview' => true,
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-web',
        'path' => '/module/web/cs-seo',
        'labels' => 'cs_seo.modules.web',
        'extensionName' => 'CsSeo',
    ],
    'file_CsSeoModFile' => [
        'parent' => 'file',
        'position' => ['after' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-file',
        'path' => '/module/file/cs-seo',
        'labels' =>'cs_seo.modules.file',
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleFileController::class => 'showEmptyImageAlt,update',
        ],
    ],
];

foreach (ModuleWebController::$menuActions as $key => $action) {
    $csSeoModules['web_CsSeoMod1_' . $action] = [
        'parent' => 'web_CsSeoMod1',
        'position' => ['before' => '*'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-' . $key,
        'path' => '/module/web/cs-seo/' . strtolower((string)preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', (string)$action)),
        'labels' => 'cs_seo.modules.web.' . $key,
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleWebController::class => [
                $action,
            ],
        ],
    ];
}

return $csSeoModules;
