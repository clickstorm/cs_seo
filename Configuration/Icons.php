<?php

use Clickstorm\CsSeo\Controller\ModuleContentController;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$moduleIcons = [
    'tx-cssseo-module-content' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_content.svg',
    ],
    'tx-cssseo-module-media' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_media.svg',
    ],
];

foreach (ModuleContentController::$menuActions as $key => $action) {
    $moduleIcons['tx-cssseo-module-' . $key] = [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod_content_' . $key . '.svg',
    ];
}

return $moduleIcons;
