<?php

namespace Clickstorm\CsSeo\Hook;


use Clickstorm\CsSeo\Controller\EvaluationController;
use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use Clickstorm\CsSeo\Utility\EvaluationUtility;
use Clickstorm\CsSeo\Utility\FrontendPageUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class pageHook {

	/**
	 * @var StandaloneView
	 */
	protected $view;

	/**
	 * Load the necessary css
	 *
	 * This will only be done when the referenced record is available
	 *
	 * @return void
	 */
	protected function loadCss()
	{
		// @todo Set to TRUE when finished
		$compress = false;
		$cssFiles = array(
			'PageHook.css'
		);
		$baseUrl = ExtensionManagementUtility::extRelPath('cs_seo') . 'Resources/Public/CSS/';
		// Load the wizards css
		foreach ($cssFiles as $cssFile) {
			$this->getPageRenderer()->addCssFile($baseUrl . $cssFile, 'stylesheet', 'all', '', $compress, false);
		}
	}

	/**
	 * Load the necessary javascript
	 *
	 * This will only be done when the referenced record is available
	 *
	 * @return void
	 */
	protected function loadJavascript()
	{
		$compress = true;
		$javascriptFiles = array(
			'jquery.cs_seo.page_hook.js'
		);
		// Load jquery
		$this->getPageRenderer()->loadJquery();
		// Load the wizards javascript
		$baseUrl = ExtensionManagementUtility::extRelPath('cs_seo') . 'Resources/Public/JavaScript/';
		foreach ($javascriptFiles as $javascriptFile) {
			$this->getPageRenderer()->addJsFile($baseUrl . $javascriptFile, 'text/javascript', $compress, false, '', false, '|', true);
		}
	}


	/**
	 * Add sys_notes as additional content to the footer of the page module
	 *
	 * @param array $params
	 * @param PageLayoutController $parentObject
	 * @return string
	 */
	public function render(array $params = array(), PageLayoutController $parentObject)
	{
		if($this->pageCanBeIndexed($parentObject->pageinfo)) {
			// template
			$this->loadCss();
			$this->loadJavascript();

			$this->view = GeneralUtility::makeInstance(StandaloneView::class);
			$this->view->setFormat('html');
			$this->view->getRequest()->setControllerExtensionName('cs_seo');
			$this->view->setLayoutRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']);
			$this->view->setPartialRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Partials/']);
			$this->view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/PageHook.html');

			$this->view->assignMultiple([
				'results'=> $this->getResults($parentObject->id),
				'page' => $parentObject->pageinfo
			]);

			return $this->view->render();
		}

	}

	/**
	 * @param array $page
	 * @return bool
	 */
	public function pageCanBeIndexed($page) {
		if($page['doktype'] == 1) {
			return true;
		}
		return false;
	}

	/**
	 * @return PageRenderer
	 */
	protected function getPageRenderer()
	{
		if (!isset($this->pageRenderer)) {
			$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		}
		return $this->pageRenderer;
	}

	/**
	 * @return PageRenderer
	 */
	protected function getResults($pageUid)
	{
		$results = [];
		$where = 'uid_foreign = ' . $pageUid;
		$where .= ' AND tablenames = "pages"';

		$res = $this->getDatabaseConnection()->exec_SELECTquery(
			'results',
			'tx_csseo_domain_model_evaluation',
			$where
		);
		while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
			$results = unserialize($row['results']);
		}
		return $results;
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