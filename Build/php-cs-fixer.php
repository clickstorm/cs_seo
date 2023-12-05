<?php

use TYPO3\CodingStandards\CsFixerConfig;
$config = CsFixerConfig::create();
$config->getFinder()->exclude(['var', 'Resources/Private/CodeTemplates']);
return $config;
