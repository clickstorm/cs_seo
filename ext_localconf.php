<?php
defined('TYPO3_MODE') || die('Access denied.');

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_csseo_title,description,tx_csseo_title_only,tx_csseo_og_title, 
tx_csseo_og_description, tx_csseo_tw_title, tx_csseo_tw_description, tx_csseo_tw_creator, tx_csseo_canonical';