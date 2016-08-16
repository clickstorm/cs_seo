<?php

namespace Clickstorm\CsSeo\Hook;


use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Core\Page\PageRenderer;

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
		
		$dom = new \DOMDocument;
		@$dom->loadHTML(file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/index.php?id=1&L=1'));
		$h1 = 0;
		foreach($dom->getElementsByTagName('h1') as $heading) {
			$h1++;
		}
		return 'h1 count: ' . $h1;
	}
}