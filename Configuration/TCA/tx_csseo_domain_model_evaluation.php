<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation',
        'label' => 'results',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',

        'hideTable' => true,

        'delete' => 'deleted',

        'security' => [
            'ignorePageTypeRestriction' => true,
        ],

        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:cs_seo/Resources/Public/Icons/mod.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'results'],
    ],
    'palettes' => [
    ],
    'columns' => [
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'results' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation.results',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'url' => [
            'label' => 'LLL:EXT:cs_seo/Resources/Private/Language/locallang_db.xlf:tx_csseo_domain_model_evaluation.url',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'uid_foreign' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tablenames' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
