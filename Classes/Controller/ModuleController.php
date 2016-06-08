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

use Clickstorm\CsSeo\View\PageInfoView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Lang\LanguageService;

class ModuleController extends ActionController {

	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected $dataHandler;

	public function pageMetaAction() {
		$fieldNames = ['title', 'tx_csseo_title', 'tx_csseo_title_only', 'description'];

		$this->processFields($fieldNames);
	}

	public function pageOpenGraphAction() {
		$fieldNames = ['title', 'tx_csseo_og_title', 'tx_csseo_og_description', 'tx_csseo_og_image'];

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
		$this->getDataHandler()->datamap = $data;
		$this->getDataHandler()->process_datamap();
	}

	protected function processFields($fieldNames) {
		$columnDefs = [];

		foreach ($fieldNames as $fieldName) {
			$columnDefs[] = '{
				field: \'' . $fieldName. '\', 
				displayName: \'' . $this->getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$fieldName]['label']) . '\',
				editableCellTemplate: \'<div><form name="inputForm"><input type="INPUT_TYPE" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-init="grid.appScope.prbValue = MODEL_COL_FIELD.length" ng-keyup="grid.appScope.prbValue = MODEL_COL_FIELD.length"></form></div>\'
			}';
		}

		$this->pageRepository->sys_language_uid = 1;

		$page = $this->pageRepository->getPage(GeneralUtility::_GP('id'));
		$pages = $this->getPageTree($page);
		$pageJSON = '
			{
				data:' . json_encode($pages) .',
				columnDefs: [' . implode(',', $columnDefs). '],
				enableSorting: true,
				showTreeExpandNoChildren: false,
				enableGridMenu: true
			}
		';
		$this->view->assign('pageJSON', $pageJSON);
	}

	protected function getPageTree($page, $pages = [], $depth = 2, $level = 0){
		$depth--;
		$fields = '*';
		$sortField = 'sorting';
		$page['$$treeLevel'] = $level;
		$pages[] = $page;
		if($depth >= 0) {
			$subPages = $this->pageRepository->getMenu($page['uid'],$fields,$sortField);
			if(count($subPages) > 0) {
				$level++;
				foreach ($subPages as &$subPage) {
					$pages = $this->getPageTree($subPage, $pages, $depth, $level);
				}
			}
		}
		return $pages;
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
	 * Returns the language service
	 * @return LanguageService
	 */
	protected function getLanguageService()
	{
		return $GLOBALS['LANG'];
	}
}