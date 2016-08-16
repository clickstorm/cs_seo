<?php

namespace Clickstorm\CsSeo\Hook;


use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class pageHook {
	/**
	 * @var StandaloneView
	 */
	protected $view;

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
		$this->view = GeneralUtility::makeInstance(StandaloneView::class);
		$this->view ->setFormat('html');
		$this->view ->setLayoutRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']);
		$this->view ->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/PageHook.html');
		return $this->view ->render();
	}
}