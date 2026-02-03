<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tx-cssseo-module-web' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
    ],
    'tx-cssseo-module-file' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cs_seo/Resources/Public/Icons/modFile.svg',
    ],
];
