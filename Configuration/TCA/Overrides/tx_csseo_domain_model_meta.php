<?php

use Clickstorm\CsSeo\Utility\ConfigurationUtility;

$extConf = ConfigurationUtility::getEmConfiguration();

if (!empty($extConf['forceMinDescription'])) {
    $GLOBALS['TCA']['tx_csseo_domain_model_meta']['columns']['description']['config']['min'] = $extConf['minDescription'];
}
