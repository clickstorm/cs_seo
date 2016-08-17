<?php
defined('TYPO3_MODE') || die('Access denied.');

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_csseo_title,description,tx_csseo_title_only,tx_csseo_keyword,tx_csseo_og_title, 
tx_csseo_og_description, tx_csseo_tw_title, tx_csseo_tw_description, tx_csseo_tw_creator, tx_csseo_canonical';



if (TYPO3_MODE === 'BE') {

	// Hook into the page module
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][$_EXTKEY] =
		\Clickstorm\CsSeo\Hook\PageHook::class . '->render';

	// add scheduler task
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$_EXTKEY] =
		\Clickstorm\CsSeo\Command\EvaluationCommandController::class;
}