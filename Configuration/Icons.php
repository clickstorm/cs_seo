<?php

use Clickstorm\CsSeo\Controller\ModuleWebController;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$moduleIcons = [
    'tx-cssseo-module-web' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_web.svg',
    ],
    'tx-cssseo-module-file' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_file.svg',
    ],
];

foreach (ModuleWebController::$menuActions as $key => $action) {
    $moduleIcons['tx-cssseo-module-' . $key] = [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_web_' . $key . '.svg',
    ];
}

return $moduleIcons;
