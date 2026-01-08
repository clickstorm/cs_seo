<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

defined('TYPO3') || die();

$extConf = ConfigurationUtility::getEmConfiguration();

if (empty($extConf['disableCharCounter'])) {
    // Add counter char wizard to the "alternative" field (ALT text)
    $GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config']['fieldWizard']['txCsseoCharCounter'] = [
        'renderType' => 'txCsseoCharCounter',
        'options' => [],
    ];
}
