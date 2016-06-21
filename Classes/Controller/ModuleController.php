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
	 * @var array
	 */
	protected $modParams = ['id' => 0, 'lang' => 0, 'depth' => 1];

	/**
	 * @var array
	 */
	protected $languages = [];

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

		// initialize settings of the module
		$this->initializeModParams();

		// get languages
		$this->languages = $this->getLanguages();
	}

	/**
	 * initialize the settings for the current view
	 *
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
	 */
	protected function initializeModParams() {
		foreach ($this->modParams as $name => $value) {
			$this->modParams[$name] = ((int)GeneralUtility::_GP($name) > 0)
				? (int) GeneralUtility::_GP($name)
				: $this->getBackendUser()->getSessionData(self::SESSION_PREFIX . $name);
			if ($this->request->hasArgument($name)) {
				$this->modParams[$name] = (int)$this->request->getArgument($name);
			}
			$this->getBackendUser()->setAndSaveSessionData(self::SESSION_PREFIX . $name, $this->modParams[$name]);
		}
	}

	/**
	 * Show SEO fields
	 */
	public function pageMetaAction() {
		$fieldNames = ['title', 'tx_csseo_title', 'tx_csseo_title_only', 'description'];

		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ContentObjectRenderer::class);

		// get TypoScript
		$backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
		$fullTS = $backendConfigurationManager->getTypoScriptSetup();

		// preview settings
		$previewSettings = [];
		$previewSettings['siteTitle'] = $fullTS['sitetitle'];
		$previewSettings['pageTitleFirst'] = $fullTS['config.']['pageTitleFirst'];
		$previewSettings['pageTitleSeparator'] = $cObj->stdWrap($fullTS['config.']['pageTitleSeparator'], $fullTS['config.']['pageTitleSeparator.']);

		if($previewSettings['pageTitleFirst']) {
			$previewSettings['siteTitle'] = $previewSettings['pageTitleSeparator'] . $previewSettings['siteTitle'];
		} else {
			$previewSettings['siteTitle'] .= $previewSettings['pageTitleSeparator'];
		}
		
		$this->view->assign('previewSettings', json_encode($previewSettings));
		
		$this->processFields($fieldNames);
	}

	/**
	 * Show Open Graph properties
	 */
	public function pageIndexAction() {
		$fieldNames = ['title', 'tx_csseo_canonical', 'tx_csseo_no_index', 'no_search'];

		$this->processFields($fieldNames);
	}

	/**
	 * Show Open Graph properties
	 */
	public function pageOpenGraphAction() {
		$fieldNames = ['title', 'tx_csseo_og_title', 'tx_csseo_og_description', 'tx_csseo_og_image'];

		$this->processFields($fieldNames);
	}

	/**
	 * Show Twitter Cards properties
	 */
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

	/**
	 * process all fields for the UI grid JSON
	 * @param $fieldNames
	 */
	protected function processFields($fieldNames) {
		// build the rows
		if($this->modParams['id'] == 0) {
			return;
		}

		// build the columns
		$columnDefs = [];
		foreach ($fieldNames as $fieldName) {
			$columnDefs[] = $this->getColumnDefinition($fieldName);
		}

		// fetch the rows
		if($this->modParams['lang'] > 0) {
			$this->pageRepository->sys_language_uid = $this->modParams['lang'];
			$columnDefs[] = $this->getColumnDefinition('sys_language_uid');
		}
		
		$page = $this->pageRepository->getPage($this->modParams['id']);
		$rowEntries = $this->getPageTree($page, $this->modParams['depth']);

		$this->view->assignMultiple([
			'pageJSON' => $this->buildGridJSON($rowEntries, $columnDefs),
			'depth' => $this->modParams['depth'],
			'lang' => $this->modParams['lang'],
			'languages' => $this->languages
		]);
	}

	/**
	 * returns the final JSON incl. settings for the UI Grid
	 *
	 * @param $rowEntries
	 * @param $columnDefs
	 * @return string
	 */
	protected function buildGridJSON($rowEntries, $columnDefs) {
		return '
			{
				data:' . json_encode($rowEntries) .',
				columnDefs: [' . implode(',', $columnDefs). '],
				enableSorting: true,
				showTreeExpandNoChildren: false,
				enableGridMenu: true,
				expandAll: true,
				enableFiltering: true,
				i18n: \'' . $GLOBALS['LANG']->lang . '\',
				cellEditableCondition: function($scope) {
					return ($scope.row.entity.doktype == "1" || $scope.row.entity.doktype == "6")
				}
			}
		';
	}

	/**
	 * get the UI grid column definition for the current field
	 * @param $fieldName
	 * @return mixed
	 */
	protected function getColumnDefinition($fieldName) {
		$columnDef = ['field' => $fieldName];
		if($fieldName == 'sys_language_uid') {

		} else {
			$columnDef['displayName'] = $this->getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$fieldName]['label']);
			switch ($GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['type']) {
				case 'check':
					$columnDef['type'] = 'boolean';
					$columnDef['width'] = 100;
					$columnDef['cellTemplate'] = '<div class="ui-grid-cell-contents ng-binding ng-scope text-center"><span class="glyphicon glyphicon-{{row.entity[col.field] == true ? \'ok\' : \'remove\'}}"></span></div>';
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm" class="text-center"><input type="checkbox" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-click="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
					$columnDef['enableFiltering'] = false;
					break;
				case 'inline':
					$columnDef['type'] = 'object';
					$columnDef['width'] = 100;
					$columnDef['enableFiltering'] = false;
					break;
				case 'text':
					$columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm"><textarea class="form-control" ng-maxlength="' . $columnDef['max'] . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
					break;
				default:
					$columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
					$columnDef['editableCellTemplate'] = '<div><form name="inputForm" ng-model="form"><input type="INPUT_TYPE" class="form-control" ng-maxlength="' . $columnDef['max'] . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
			}
		}

		switch ($fieldName) {
			case 'title':
				$columnDef['cellTemplate'] = '<div class="ui-grid-cell-contents ng-binding ng-scope"><span ng-repeat="i in grid.appScope.rangeArray | limitTo: row.entity.level">&nbsp;&nbsp;</span>{{row.entity.title}}</div>';
				break;
			case 'tx_csseo_title':
				$columnDef['min'] = 35;
				break;
			case 'description':
				$columnDef['min'] = 120;
				break;
			case 'sys_language_uid':
				$columnDef['displayName'] = $this->getLanguageService()->sL($GLOBALS['TCA']['pages_language_overlay']['columns'][$fieldName]['label']);
				$columnDef['width'] = 100;
				$columnDef['type'] = 'object';
				$columnDef['enableFiltering'] = false;
				break;
		}

		return json_encode($columnDef);
	}

	/**
	 * recursive function for building a page array
	 *
	 * @param array $page the current page
	 * @param int $depth the current depth
	 * @param array $pages contains all pages so far
	 * @param int $level the tree level required for the UI grid
	 * @return array
	 */
	protected function getPageTree($page, $depth, $pages = [], $level = 0){
		// default query settings
		$fields = '*';
		$sortField = 'sorting';

		// decrease the depth
		$depth--;

		// add the current language value
		if($this->modParams['lang'] > 0) {
			$page['sys_language_uid'] = $this->languages[$page['_PAGES_OVERLAY_LANGUAGE']?:0];
		}

		$page['level'] = $level;

		// add the page to the pages array
		$pages[] = &$page;

		// fetch subpages and set the treelevel
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