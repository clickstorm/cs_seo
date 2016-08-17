<?php

namespace Clickstorm\CsSeo\Hook;


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
	 * Add sys_notes as additional content to the footer of the page module
	 *
	 * @param array $params
	 * @param PageLayoutController $parentObject
	 * @return string
	 */
	public function render(array $params = array(), PageLayoutController $parentObject)
	{
		// template
		$this->loadCss();
		$this->view = GeneralUtility::makeInstance(StandaloneView::class);
		$this->view->setFormat('html');
		$this->view->getRequest()->setControllerExtensionName('cs_seo');
		$this->view->setLayoutRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']);
		$this->view->setPartialRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Partials/']);
		$this->view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/PageHook.html');

		/** @var FrontendPageUtility $frontendPageUtility */
		$frontendPageUtility = GeneralUtility::makeInstance(FrontendPageUtility::class, $parentObject->id, $parentObject->MOD_SETTINGS['language']);
		$html = $frontendPageUtility->getHTML();

		/** @var EvaluationUtility $evaluationUtility */
		$evaluationUtility = GeneralUtility::makeInstance(EvaluationUtility::class, $html, $parentObject->pageinfo['tx_csseo_keyword']);
		$results = $evaluationUtility->evaluate();

		$this->view->assign('results', $results);

		return $this->view->render();
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
}