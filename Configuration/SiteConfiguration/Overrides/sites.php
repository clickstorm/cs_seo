<?php

/**
 * x-default field
 */
$GLOBALS['SiteConfiguration']['site']['columns']['txCsseoXdefault'] = [
    'label' => 'x-default',
    'description' => 'signal for search engines to use this language if no other is better suited',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'sys_language',
        'default' => 0,
        'items' => [
            [
                0 => 'Default Language',
                1 => 0
            ]
        ],
        'min' => 1,
        'max' => 1
    ],
];

// add the new field
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
    ' languages,',
    ' languages, txCsseoXdefault,',
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);