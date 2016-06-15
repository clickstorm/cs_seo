<?php

namespace Clickstorm\CsSeo\Controller;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class ModuleController extends ActionController {

	/**
	 * @var string prefix for session
	 */
	const SESSION_PREFIX = 'tx_csseo_';

	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected $dataHandler;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $lang;

	/**
	 * @var int
	 */
	protected $depth = 2;

	/**
	 * Initialize action
	 *
	 * @return void
	 */
	protected function initializeAction()
	{
		// initialize page/be_user TSconfig settings
		$this->modSharedTSconfig = BackendUtility::getModTSconfig($this->id, 'mod.SHARED');
		$this->modTSconfig = BackendUtility::getModTSconfig($this->id, 'mod.' . $this->moduleName);

		// determine id parameter
		$this->id = (int)GeneralUtility::_GP('id');
		if ($this->request->hasArgument('id')) {
			$this->id = (int)$this->request->getArgument('id');
		}

		// determine depth parameter
		$this->depth = ((int)GeneralUtility::_GP('depth') > 0)
			? (int) GeneralUtility::_GP('depth')
			: $this->getBackendUser()->getSessionData(self::SESSION_PREFIX . 'depth');
		if ($this->request->hasArgument('depth')) {
			$this->depth = (int)$this->request->getArgument('depth');
		}
		$this->getBackendUser()->setAndSaveSessionData(self::SESSION_PREFIX . 'depth', $this->depth);

		// determine depth parameter
		$this->lang = ((int)GeneralUtility::_GP('lang') > 0)
			? (int) GeneralUtility::_GP('lang')
			: $this->getBackendUser()->getSessionData(self::SESSION_PREFIX . 'lang');
		if ($this->request->hasArgument('lang')) {
			$this->lang = (int)$this->request->getArgument('lang');
		}
		$this->getBackendUser()->setAndSaveSessionData(self::SESSION_PREFIX . 'lang', $this->lang);
	}

	public function pageMetaAction() {
		$fieldNames = ['title', 'tx_csseo_title', 'tx_csseo_title_only', 'description'];

		$backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
		$fullTS = $backendConfigurationManager->getTypoScriptSetup();

		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ContentObjectRenderer::class);
		
		// template1
		$wizardView = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
		$wizardView->setFormat('html');
		$wizardView->setLayoutRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']);
		$wizardView->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/Wizard.html');

		$wizardView->assignMultiple([
			'config' => $fullTS['config.'],
			'pageTitleSeparator' => $cObj->stdWrap($fullTS['config.']['pageTitleSeparator'], $fullTS['config.']['pageTitleSeparator.']),
			'siteTitle' => $fullTS['sitetitle'],
			'data' => 1
		]);
		
		$this->view->assign('wizardView', $wizardView->render());

		$this->processFields($fieldNames);
	}

	public function pageOpenGraphAction() {
		$fieldNames = ['title', 'tx_csseo_og_title', 'tx_csseo_og_description', 'tx_csseo_og_image'];

		$this->processFields($fieldNames);
	}

	public function pageTwitterCardsAction() {
		$fieldNames = ['title', 'tx_csseo_tw_title', 'tx_csseo_tw_description', 'tx_csseo_tw_creator', 'tx_csseo_tw_image'];

		$this->processFields($fieldNames);
	}

	/**
	 * Renders the menu so that it can be returned as response to an AJAX call
	 *
	 * @param array $params Array of parameters from the AJAX interface, currently unused
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj Object of type AjaxRequestHandler
	 * @return void
	 */
	public function update($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {

		// get parameter
		$postdata = file_get_contents("php://input");
		$attr = json_decode($postdata);

		// prepare data array
		$tableName = 'pages';
		$uid = $attr->entry->uid;
		$field = $attr->entry->field;

		// check for language overlay
		if($attr->entry->_PAGES_OVERLAY && isset($GLOBALS['TCA']['pages_language_overlay']['columns'][$field])) {
			$tableName = 'pages_language_overlay';
			$uid = $attr->entry->_PAGES_OVERLAY_UID;
		}

		// update map
		$data[$tableName][$uid][$attr->field] = $attr->value;

		// update data
		$dataHandler = $this->getDataHandler();
		$dataHandler->datamap = $data;
		$dataHandler->process_datamap();
		if(!empty($dataHandler->errorLog)) {
			$ajaxObj->addContent('Failed', 'Error');
		}
	}

	protected function processFields($fieldNames) {
		$columnDefs = [];

		foreach ($fieldNames as $fieldName) {
			$columnDef = [
				'field' => $fieldName,
				'displayName' => $this->getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$fieldName]['label'])
			];
			switch ($GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['type']) {
				case 'check':
					$columnDef['type'] = 'boolean';
					$columnDef['width'] = 100;
					$columnDef['cellTemplate'] = '<div class="ui-grid-cell-contents ng-binding ng-scope">{{row.entity[col.field] == true ? "1" : "0"}}</div>';
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm"><input type="checkbox" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-init="grid.appScope.currentValue = MODEL_COL_FIELD" ng-click="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
					break;
				case 'inline':
					$columnDef['type'] = 'object';
					$columnDef['width'] = 100;
					break;
				case 'text':
					$columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm"><textarea class="form-control" ng-maxlength="' . $columnDef['max'] . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-init="grid.appScope.currentValue = MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
					break;
				default:
					$columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm" ng-model="form"><input type="INPUT_TYPE" class="form-control" ng-maxlength="' . $columnDef['max'] . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-init="grid.appScope.currentValue = MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
			}
			$columnDefs[] = json_encode($columnDef);
		}

		if($this->lang > 0) {
			$this->pageRepository->sys_language_uid = $this->lang;
			$this->view->assign('lang', $this->lang);
		}

		if($this->id > 0) {
			$page = $this->pageRepository->getPage($this->id);
			if($page) {
				$pages = $this->getPageTree($page, $this->depth);
				$pageJSON = '
				{
					data:' . json_encode($pages) .',
					columnDefs: [' . implode(',', $columnDefs). '],
					enableSorting: true,
					showTreeExpandNoChildren: false,
					enableGridMenu: true,
					expandAll: true
				}
			';
				$this->view->assignMultiple([
					'pageJSON' => $pageJSON,
					'depth' => $this->depth,
					'lang' => $this->lang,
					'languages' => $this->getLanguages()
				]);
			}
		}

	}

	protected function getPageTree($page, $depth, $pages = [], $level = 0){
		$depth--;
		$fields = '*';
		$sortField = 'sorting';
		$pages[] = &$page;
		if($depth > 0) {
			$subPages = $this->pageRepository->getMenu($page['uid'],$fields,$sortField);
			if(count($subPages) > 0) {
				$page['$$treeLevel'] = $level;
				$level++;
				foreach ($subPages as &$subPage) {
					$pages = $this->getPageTree($subPage, $depth, $pages, $level);
				}
			}
		}
		return $pages;
	}

	/**
	 * Returns a SQL query for selecting sys_language records.
	 *
	 * @return string Return query string.
	 */
	public function getLanguages()
	{
		$languages[0] = 'Default';

		$res = $this->getDatabaseConnection()->exec_SELECTquery(
			'sys_language.*',
			'sys_language',
			'sys_language.hidden=0',
			'',
			'sys_language.title'
		);
		while ($lRow = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
			if ($this->getBackendUser()->checkLanguageAccess($lRow['uid'])) {
				$languages[$lRow['uid']] = $lRow['hidden'] ? '(' . $lRow['title'] . ')' : $lRow['title'];
			}
		}
		// Setting alternative default label:
		if (($this->modSharedTSconfig['properties']['defaultLanguageLabel'] || $this->modTSconfig['properties']['defaultLanguageLabel'])) {
			$languages[0] = $this->modTSconfig['properties']['defaultLanguageLabel'] ? $this->modTSconfig['properties']['defaultLanguageLabel'] : $this->modSharedTSconfig['properties']['defaultLanguageLabel'];
		}
		return $languages;
	}

	/**
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	public function getDataHandler()
	{
		if (!isset($this->dataHandler)) {
			$this->dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
			$this->dataHandler->start(null,null);
		}
		return $this->dataHandler;
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

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser()
	{
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Returns the language service
	 * @return LanguageService
	 */
	protected function getLanguageService()
	{
		return $GLOBALS['LANG'];
	}
}