<?php

namespace Clickstorm\CsSeo\Command;

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

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Service\FrontendPageService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class EvaluationCommandController
 * @package Clickstorm\CsSeo\Command
 */
class EvaluationCommandController extends CommandController {

	/**
	 * @var \Clickstorm\CsSeo\Domain\Repository\EvaluationRepository
	 * @inject
	 */
	protected $evaluationRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var string
	 */
	protected $tableName = 'pages';

	/**
	 * @param int $uid
	 * @param string $tableName
	 */
	public function updateCommand($uid = 0, $tableName = '') {
		if(!empty($tableName)) {
			$this->tableName = $tableName;
		}
		$this->processResults($uid);
	}

	/**
	 * make the ajax update
	 *
	 * @param array $params Array of parameters from the AJAX interface, currently unused
	 * @param AjaxRequestHandler $ajaxObj Object of type AjaxRequestHandler
	 * @return void
	 */
	public function ajaxUpdate($params = array(), AjaxRequestHandler &$ajaxObj = NULL) {
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->evaluationRepository = $this->objectManager->get(EvaluationRepository::class);
		$this->persistenceManager = $this->objectManager->get(PersistenceManager::class);

		// get parameter
		if(empty($params)) {
			$uid = $GLOBALS['GLOBALS']['HTTP_POST_VARS']['uid'];
		} else {
			$attr = $params['request']->getParsedBody();
			$uid = $attr['uid'];
		}
		$this->processResults($uid);

		$ajaxObj->addContent('uid', $uid);
	}

	/**
	 * @param int $uid
	 * @param bool $localized
	 */
	protected function processResults($uid = 0, $localized = false) {
		$query = $this->buildQuery($uid, $localized);
		$items = $this->getAllItems($query);
		$this->updateResults($items);

		if(!$localized) {
			$this->processResults($uid, true);
		}
	}

	/**
	 * @param $items
	 */
	protected function updateResults($items) {
		foreach ($items as $item) {
			/** @var FrontendPageService $frontendPageService */
			$frontendPageService = GeneralUtility::makeInstance(FrontendPageService::class, $item);
			$html = $frontendPageService->getHTML();

			if (!empty($html)) {
				/** @var EvaluationService $evaluationUtility */
				$evaluationUtility = GeneralUtility::makeInstance(EvaluationService::class);
				$results = $evaluationUtility->evaluate($html, $item['tx_csseo_keyword']);

				$this->saveChanges($results, $item['uid']);
			}
		}
	}

	/**
	 * store the results in the db
	 * @param $results
	 * @param $uidForeign
	 */
	protected function saveChanges($results, $uidForeign) {
		/**
		 * @var Evaluation $evaluation
		 */
		$evaluation = $this->evaluationRepository->findByUidForeignAndTableName($uidForeign, $this->tableName);

		if(!$evaluation) {
			$evaluation = GeneralUtility::makeInstance(Evaluation::class);
			$evaluation->setUidForeign($uidForeign);
			$evaluation->setTablenames($this->tableName);
		}

		$evaluation->setResults($results);

		if($evaluation->_isNew()) {
			$this->evaluationRepository->add($evaluation);
		} else {
			$this->evaluationRepository->update($evaluation);
		}
		$this->persistenceManager->persistAll();
	}

	/**
	 * @param $uid
	 * @param bool $localizations
	 * @return string
	 */
	protected function buildQuery($uid, $localizations = false) {
		$constraints = ['1'];
		$tcaCtrl = $GLOBALS['TCA'][$this->tableName]['ctrl'];

		// only with doktype page
		if($this->tableName == 'pages') {
			$constraints[] =  'doktype = 1';
		}

		// check localization
		if($localizations) {
			if($tcaCtrl['transForeignTable']) {
				$this->tableName = $tcaCtrl['transForeignTable'];
				$tcaCtrl['transOrigPointerField'] = 'pid';
			} else {
				if($tcaCtrl['languageField']) {
					$constraints[] = $tcaCtrl['languageField'] . ' > 0';
				}
			}
		}

		// if single uid
		if($uid > 0) {
			if($localizations) {
				$constraints[] =  $tcaCtrl['transOrigPointerField'] . ' = ' . $uid;
			} else {
				$constraints[] =  'uid = ' . $uid;
			}
		}

		return implode($constraints, ' AND ') . BackendUtility::BEenableFields($this->tableName);
	}

	/**
	 * @param $where
	 * @return array
	 */
	protected function getAllItems($where) {
		$items = [];

		$res = $this->getDatabaseConnection()->exec_SELECTquery(
			'*',
			$this->tableName,
			$where
		);
		while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
			$items[] = $row;
		}
		return $items;
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
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