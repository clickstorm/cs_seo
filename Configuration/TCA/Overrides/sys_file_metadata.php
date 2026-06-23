<?php

declare(strict_types=1);

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

defined('TYPO3') || die();

$extConf = ConfigurationUtility::getEmConfiguration();

if (!empty($extConf['enableMultilineAltText'])) {
    // Switch the ALT text field to a multiline textarea.
    $GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config'] = array_replace(
        $GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config'],
        [
            'type' => 'text',
            'rows' => 3,
            'cols' => 40,
        ]
    );

    // Remove keys that only belong to type=input
    unset($GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config']['size']);
}

if (empty($extConf['disableCharCounter'])) {
    // Add counter char wizard to the "alternative" field (ALT text)
    $GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config']['fieldWizard']['txCsseoCharCounter'] = [
        'renderType' => 'txCsseoCharCounter',
        'options' => [],
    ];
}
