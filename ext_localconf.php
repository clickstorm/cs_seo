<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'] .= ',tx_csseo_title,description,tx_csseo_title_only,tx_csseo_keyword,tx_csseo_og_title, 
tx_csseo_og_description, tx_csseo_tw_title, tx_csseo_tw_description, tx_csseo_tw_creator, tx_csseo_canonical';

$confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (TYPO3_MODE === 'BE') {

	// Hook into the page module
	if(!isset($confArray['inPageModule']) || $confArray['inPageModule'] < 2) {
		$hook = ($confArray['inPageModule'] == 1) ? 'drawFooterHook' : 'drawHeaderHook';

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php'][$hook][$_EXTKEY] =
			\Clickstorm\CsSeo\Hook\PageHook::class . '->render';
	}

	// add scheduler task
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$_EXTKEY] =
		\Clickstorm\CsSeo\Command\EvaluationCommandController::class;

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsDisallowAllEvaluator'] = '';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['Clickstorm\\CsSeo\\Evaluation\\TCA\\RobotsExistsEvaluator'] = '';
}


// realURL autoconf
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
	if($confArray['realURLAutoConf']) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['index']['robots.txt'] = [
			'keyValues' => [
				'type' => 656
			]
		];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['index']['sitemap.xml'] = [
			'keyValues' => [
				'type' => 655
			]
		];
	}
}