<?php
defined('TYPO3_MODE') || die('Access denied.');

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_csseo_title,description,tx_csseo_title_only,tx_csseo_og_title, 
tx_csseo_og_description, tx_csseo_tw_title, tx_csseo_tw_description, tx_csseo_tw_creator, tx_csseo_canonical';

// Add field to TCA
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass']['cs_seo'] =
	'Clickstorm\\CsSeo\\Hooks\\TCA';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['cs_seo'] =
	'Clickstorm\\CsSeo\\Hooks\\TCA';

// TPYO3 7.6

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Clickstorm\CsSeo\Backend\FormDataProvider\MetaRowInitializeNew::class] = [
	'depends' => [
		TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
	]
];