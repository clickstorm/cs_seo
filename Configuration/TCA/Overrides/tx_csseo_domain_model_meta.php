<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
$extConf = ConfigurationUtility::getEmConfiguration();
$minDescriptionLength = (int)($extConf['minDescription'] ?? 0);
$maxDescriptionLength = (int)($extConf['maxDescription'] ?? 0);

if (empty($extConf['disableCharCounter'])) {
    $GLOBALS['TCA']['tx_csseo_domain_model_meta']['columns']['description']['config']['fieldWizard']['txCsseoCharCounter'] = [
        'renderType' => 'txCsseoCharCounter',
        'options' => [
            'minChars' => $minDescriptionLength,
            'maxChars' => $maxDescriptionLength,
        ],
    ];
}
