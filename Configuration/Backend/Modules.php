<?php

use Clickstorm\CsSeo\Controller\ModuleWebController;
use Clickstorm\CsSeo\Controller\ModuleFileController;

$csSeoModules =  [
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
            ModuleWebController::class => [
                'pageMeta',
                'pageIndex',
                'pageOpenGraph',
                'pageTwitterCards',
                'pageStructuredData',
                'pageResults',
                'pageEvaluation',
            ]
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

foreach (ModuleWebController::$menuActions as $action) {
    $csSeoModules['web_CsSeoMod1_' .  $action] = [
        'parent' => 'web_CsSeoMod1',
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'tx-cssseo-module-web',
        'path' => '/module/web/cs-seo/' . strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $action)),
        'labels' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:layouts.module.action.' . $action,
        'extensionName' => 'CsSeo',
        'controllerActions' => [
            ModuleWebController::class => [
                $action
            ]
        ]
    ];
}


return $csSeoModules;
