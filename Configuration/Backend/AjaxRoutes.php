<?php

return [
    'tx_csseo_update' => [
        'path' => '/cs_seo/update',
        'target' => \Clickstorm\CsSeo\Controller\ModuleController::class . '::update'
    ],
    'tx_csseo_evaluate' => [
        'path' => '/cs_seo/evaluate',
        'target' => \Clickstorm\CsSeo\Command\EvaluationCommandController::class . '::ajaxUpdate'
    ]
];
 