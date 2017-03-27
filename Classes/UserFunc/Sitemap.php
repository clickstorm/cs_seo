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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\DatabaseUtility;
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
class Sitemap {
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

	/**
	 * @var TypoScriptFrontendController
	 */
	protected $tsfe;


	public function main() {
		$this->tsfe = $this->getTypoScriptFrontendController();
		$this->pageRepository = $this->tsfe->sys_page;

		// set TypoScript settings and parse them for Fluid
		$this->setSettings($this->parseSettings($this->tsfe->tmpl->setup['plugin.']['tx_csseo.']['sitemap.']));

		// init fluid templates
		$this->view = GeneralUtility::makeInstance(StandaloneView::class);
		$this->view->setFormat('xml');
		$this->view->getRequest()->setControllerExtensionName('cs_seo');
		$absoluteResourcesPath = ExtensionManagementUtility::extPath('cs_seo') . 'Resources/';
		$this->view->setLayoutRootPaths([$absoluteResourcesPath . 'Private/Layouts/']);
		$this->view->setPartialRootPaths([$absoluteResourcesPath . 'Private/Partials/']);

		// switch view
		switch (GeneralUtility::_GP('tx_csseo_view')) {
			// sitemap for pages
			case 'pages':
				$this->view->setTemplatePathAndFilename(
					$absoluteResourcesPath . 'Private/Templates/Sitemap/Pages.xml'
				);
				$settings = $this->settings['pages'];

				// first get the root page
				$rootPage = $this->tsfe->sys_page->getPage($settings['rootPid']);

				// remove doktype exlude
				$this->tsfe->sys_page->where_hid_del = str_replace(
					' AND pages.doktype<200',
					'',
					$this->tsfe->sys_page->where_hid_del
				);

				// get the subpages
				$subPages = $this->getSubPages($rootPage['uid']);

				// merge all pages
				$pages = array_merge([$rootPage], $subPages);

				$this->view->assignMultiple(
					[
						'lang' => $this->tsfe->sys_language_uid,
						'pages' => $pages
					]
				);
				break;
			// sitemap for extensions
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

						if(is_array($records) && count($records) > 0) {
							foreach ($records as $key => $record) {
								$typoLinkConf['additionalParams'] = '&' . $extConf['additionalParams'] . '=' . $record['uid'];
								if ($record['lang']) {
									$typoLinkConf['additionalParams'] .= '&L=' . $this->getTypoScriptFrontendController()->sys_language_uid;
								}
								$records[$key]['loc'] = $cObject->typoLink_URL($typoLinkConf);
							}
						}

						$this->view->assignMultiple([
							'extConf' => $extConf,
							'records' => $records
						]);
					}
				}
				break;
			// list all sitemaps
			default:
				$this->view->setTemplatePathAndFilename(
					$absoluteResourcesPath . 'Private/Templates/Sitemap/ListAll.xml'
				);
				$this->view->assign('settings', $this->settings);
		}


		return $this->beautifyXML($this->view->render());
	}

	/**
	 * @param $string XML String
	 * @return string
	 */
	protected function beautifyXML($string) {
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($string);
		$dom->formatOutput = TRUE;
		return $dom->saveXml();
	}

	/**
	 * @return TypoScriptFrontendController
	 */
	protected function getTypoScriptFrontendController() {
		return $GLOBALS['TSFE'];
	}

	/**
	 * parse the TypoScript settings for Fluid
	 *
	 * @param $settings
	 * @return array
	 */
	protected function parseSettings($settings) {
		$parsedSettings = [];
		if (is_array($settings)) {
			foreach ($settings as $key => $value) {
				$key = rtrim($key, '.');
				if (!is_array($value)) {
					$parsedSettings[$key] = $value;
				} else {
					$parsedSettings[$key] = $this->parseSettings($value);
				}
			}
		}

		return $parsedSettings;
	}

	/**
	 * @param array $pages
	 * @param array $newPages
	 * @return array
	 */
	protected function getSubPages($pageUid) {
		$subPages = $this->tsfe->sys_page->getMenu(
			$pageUid,
			'*',
			'sorting',
			''
		);

		foreach ($subPages as $subPage) {
			ArrayUtility::mergeRecursiveWithOverrule(
				$subPages,
				$this->getSubPages($subPage['uid'])
			);
		}

		return $subPages;
	}

	/**
	 * @param array $extConf
	 * @return bool|array
	 */
	protected function getRecords($extConf) {
		$db = $this->getDatabaseConnection();
		if (!isset($GLOBALS['TCA'][$extConf['table']])) {
			return false;
		}

		$table = $extConf['table'];
		$where = '';
		$from = $extConf['table'];
		$select = $table . '.uid';

		$constraints = [];
		$lang = $this->getTypoScriptFrontendController()->sys_language_uid;
		$tca = $GLOBALS['TCA'][$extConf['table']];

		// storage
		if ($extConf['storagePid']) {
			$constraints[] = $table . '.pid IN (' . $extConf['storagePid'] . ')';
		}

		// lang
		$languageField = $tca['ctrl']['languageField'];
		if ($languageField) {
			$constraints[] = $table . '.' . $languageField . ' IN (' . $lang . ',-1)';
			$select .= ', ' . $table . '.' . $languageField . ' AS lang';
		}

		// categories
		if ($extConf['categories']) {
			if ($extConf['categoryField']) {
				$constraints[] = $extConf['categoryField'] . ' IN (' . $extConf['categories'] . ')';
			} elseif ($extConf['categoryMMTable']) {
				$catTable = $extConf['categoryMMTable'];
				$from .= ' LEFT JOIN ' . $catTable . ' ON ' . $table . '.uid = ' . $catTable . '.uid_foreign';
				$constraints[] = $catTable . '.uid_local IN (' . $extConf['categories'] . ')';
				if ($extConf['categoryMMTablename']) {
					$constraints[] = $catTable . '.tablenames = ' . $db->fullQuoteStr($table, $table);
				}
				if ($extConf['categoryMMFieldname']) {
					$constraints[] = $catTable . '.fieldname = ' . $db->fullQuoteStr(
							$extConf['categoryMMFieldname'],
							$table
						);
				}
			}
		}

		// lastmod
		if ($tca['ctrl']['tstamp']) {
			$select .= ', ' . $table . '.' . $tca['ctrl']['tstamp'] . ' AS lastmod';
		}

		// no index
		if ($tca['columns']['tx_csseo']) {
			$from .= ' LEFT JOIN tx_csseo_domain_model_meta ON ' . $table . '.uid = tx_csseo_domain_model_meta.uid_foreign';
			$constraints[] = '(' . $table . '.tx_csseo = 0 OR
        	 (tx_csseo_domain_model_meta.tablenames = ' . $db->fullQuoteStr($table, $table) . ' AND
        	 tx_csseo_domain_model_meta.no_index = 0))';
		}

		if ($extConf['additionalWhereClause']) {
		    $constraints[] = $extConf['additionalWhereClause'];
		}

		if (count($constraints)) {
			$where .= implode($constraints, ' AND ');
		} else {
			$where = '1=1';
		}

		$where .= $this->pageRepository->enableFields(
			$extConf['table']
		);

		return $db->exec_SELECTgetRows(
			$select,
			$from,
			$where,
			$table . '.uid'
		);
	}

	/**
	 * Returns the database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
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
}