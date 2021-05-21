<?php

return [
    'tx_csseo_update' => [
        'path' => '/cs_seo/update',
        'target' => \Clickstorm\CsSeo\Controller\ModuleWebController::class . '::update'
    ],
    'tx_csseo_evaluate' => [
        'path' => '/cs_seo/evaluate',
        'target' => \Clickstorm\CsSeo\Command\EvaluationCommand::class . '::ajaxUpdate'
    ]
];
