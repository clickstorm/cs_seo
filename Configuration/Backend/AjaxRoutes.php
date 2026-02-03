<?php

use Clickstorm\CsSeo\Command\EvaluationCommand;
use Clickstorm\CsSeo\Controller\ModuleWebController;

return [
    'tx_csseo_update' => [
        'path' => '/cs_seo/update',
        'target' => ModuleWebController::class . '::update',
    ],
    'tx_csseo_evaluate' => [
        'path' => '/cs_seo/evaluate',
        'target' => EvaluationCommand::class . '::ajaxUpdate',
    ],
];
