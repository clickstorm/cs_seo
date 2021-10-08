<?php

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['yamlConfigFile'] =
    'EXT:cs_seo/Tests/Functional/Fixtures/CsSeo/categories.yaml';

$GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters'] = 'category,category_id,param,param_to_keep';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;