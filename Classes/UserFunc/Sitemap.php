<?php
namespace Clickstorm\CsSeo\UserFunc;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Generates the sitemap.xml
 *
 * Class Sitemap
 * @package Clickstorm\CsSeo\UserFunc
 */
class Sitemap
{
	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var StandaloneView
	 */
	protected $view;

	/**
	 * @var PageRepository
	 */
	protected $pageRepository;


	public function main($p1, $p2) {
		/** @var PageRepository pageRepository */
		$this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);

		// parse and set ts settings
		$this->setSettings($this->parseSettings($p2['settings.']));

		// init fluid templates
		$this->view = GeneralUtility::makeInstance(StandaloneView::class);
		$this->view->setFormat('xml');
		$this->view->getRequest()->setControllerExtensionName('cs_seo');
		$absoluteResourcesPath = ExtensionManagementUtility::extPath('cs_seo') . 'Resources/';
		$this->view->setLayoutRootPaths([$absoluteResourcesPath . 'Private/Layouts/']);
		$this->view->setPartialRootPaths([$absoluteResourcesPath . 'Private/Partials/']);

		// switch view
		switch (GeneralUtility::_GP('tx_csseo_view')) {
			case 'pages':
				$this->view->setTemplatePathAndFilename(
					$absoluteResourcesPath . 'Private/Templates/Sitemap/Pages.xml'
				);
				$settings = $this->settings['pages'];
				$lang = GeneralUtility::_GP('lang')?:0;
				$this->getTypoScriptFrontendController()->sys_page->sys_language_uid = $lang;
				$pages = $this->getTypoScriptFrontendController()->sys_page->getMenu(
					$settings['rootPid'],
					'*',
					'sorting',
					'AND doktype = 1 AND tx_csseo_no_index = 0'
				);

				$this->view->assignMultiple([
					'lang' => $lang,
					'pages' => $pages
				]);
				break;
			case 'extension':
				$this->view->setTemplatePathAndFilename(
					$absoluteResourcesPath . 'Private/Templates/Sitemap/Extension.xml'
				);
				$extName = GeneralUtility::_GP('ext');
				if ($extName) {
					$extConf = $this->settings['extensions'][$extName];
					if ($extConf) {
						$cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
						$typoLinkConf = [
							'parameter' => $extConf['detailPid'],
							'forceAbsoluteUrl' => 1
						];
						$records = $this->getRecords($extConf);
						foreach ($records as $key => $record) {
							$typoLinkConf['additionalParams'] = '&' . $extConf['getParameter'] . '=' . $record['uid'];
							if ($record['lang']) {
								$typoLinkConf['additionalParams'] .= '&L=' . $record['lang'];
							}
							$records[$key]['loc'] = $cObject->typoLink_URL($typoLinkConf);
						}
						$this->view->assign('records', $records);
					}
				}
		    	break;
		    default: $this->view->setTemplatePathAndFilename($absoluteResourcesPath . 'Private/Templates/Sitemap/ListAll.xml');
		            $this->view->assign('settings', $this->getSettings());
	    }


	    return $this->view->render();
    }

	/**
	 * @param array $extConf
	 * @return bool|\mysqli_result|object
	 */
    protected function getRecords($extConf) {

	    if (!isset($GLOBALS['TCA'][$extConf['table']])) {
	    	return false;
	    }


	    $where = '1';
	    $select = 'uid';
	    $constraints = [];

	    if($extConf['storagePid']) {
		    $constraints[] = 'pid IN (' . $extConf['storagePid'] . ')';
	    }

	    if($extConf['languageUids']) {
	    	$languageField = $GLOBALS['TCA'][$extConf['table']]['ctrl']['languageField'];
	    	if($languageField) {
			    $constraints[] = $languageField . '= (' . $extConf['languageUids'] . ',-1)';
			    $select .= ', ' . $languageField . ' AS lang';
		    }
	    }

	    if($GLOBALS['TCA'][$extConf['table']]['ctrl']['tstamp']) {
		    $select .= ', ' . $GLOBALS['TCA'][$extConf['table']]['ctrl']['tstamp'] . ' AS lastmod';
	    }

	    if(count($constraints)) {
		    $where .= ' AND ' . implode($constraints, ' AND ');
	    }

	    $where .= $this->pageRepository->enableFields(
		    $extConf['table']
	    );

	    return $this->getDatabaseConnection()->exec_SELECTgetRows(
		    $select,
		    $extConf['table'],
		    $where
	    );
    }



	/**
	 * @return array
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param array $settings
	 */
	public function setSettings($settings) {
		$this->settings = $settings;
	}

	/**
	 * parse the TypoScript settings for Fluid
	 *
	 * @param $settings
	 * @return array
	 */
	protected function parseSettings($settings) {
		$parsedSettings = [];
		foreach ($settings as $key => $value) {
			$key = rtrim($key, '.');
			if (!is_array($value)) {
				$parsedSettings[$key] = $value;
			} else {
				$parsedSettings[$key] = $this->parseSettings($value);
			}
		}
		return $parsedSettings;
	}

	protected function getPageConstraints($includeNotInMenu = false, $includeMenuSeparator = false)
	{
		$constraints = [];

		$constraints[] = 'doktype = 1';

		if (!$includeNotInMenu) {
			$constraints[] = 'nav_hide = 0';
		}

		if (!$includeMenuSeparator) {
			$constraints[] = 'doktype != ' . PageRepository::DOKTYPE_SPACER;
		}

		return 'AND ' . implode(' AND ', $constraints);
	}

	/**
	 * @return TypoScriptFrontendController
	 */
	protected function getTypoScriptFrontendController()
	{
		return $GLOBALS['TSFE'];
	}

	/**
	 * Returns the database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection()
	{
		return $GLOBALS['TYPO3_DB'];
	}
}
