<?php
namespace Clickstorm\CsSeo\UserFunc;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *  (c) 2013 Mathias Brodala <mbrodala@pagemachine.de>, PAGEmachine AG
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

use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Frontend\Page\PageGenerator;

/**
 * Google Search Results Preview
 *
 * Class PageTitle
 * @package Clickstorm\CsSeo\UserFunc
 */
class PreviewWizard
{

    /**
     * The document template object
     *
     * Needs to be a local variable of the class.
     *
     * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public $doc;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var int
     */
    protected $typeNum = 654;

    /**
     * Constructs this view
     *
     * Defines the global variable SOBE. Normally this is used by the wizards
     * which are one file only. This view is now the class with the global
     * variable name SOBE.
     *
     * Defines the document template object.
     *
     * @see \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public function __construct()
    {
        $this->getLanguageService()->includeLLFile('EXT:cs_templates/Resources/Private/Language/locallang.xlf');
        $GLOBALS['SOBE'] = $this;
        // Define the document template object
        $this->doc = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
        $this->doc->setModuleTemplate('EXT:cs_seo/Resources/Private/Templates/PreviewWizard.html');
    }

    /**
     * @param array $cont
     * @param \TYPO3\CMS\Backend\Form\Element\InputTextElement $inputTextElement
     *
     * @return string
     */
    public function render($cont, $inputTextElement)
    {
        // Load necessary JavaScript
        $this->loadJavascript();
        // Load necessary CSS
        $this->loadCss();

        // get Content
        $content = $this->getBodyContent($cont['row'], $cont['table']);

        return $content;
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
            'jquery.cs_seo.preview.js'
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
            'Wizard.css'
        );
        $baseUrl = ExtensionManagementUtility::extRelPath('cs_seo') . 'Resources/Public/CSS/';
        // Load the wizards css
        foreach ($cssFiles as $cssFile) {
            $this->getPageRenderer()->addCssFile($baseUrl . $cssFile, 'stylesheet', 'all', '', $compress, false);
        }
    }

    /**
     * Generate the body content
     *
     * If there is an error, no reference to a record, a Flash Message will be
     * displayed
     *
     * @return string The body content
     */
    protected function getBodyContent($data, $table)
    {
        // template1
        $wizardView = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $wizardView->setFormat('html');
        $wizardView->setLayoutRootPaths([10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']);
        $wizardView->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/Wizard.html');
        
        if(strpos($data['uid'], 'NEW') === false) {

            // set pageID for TSSetup check
            $pageUid = ($table == 'pages') ? $data['uid'] : $data['pid'];
            $_GET['id'] = $pageUid;

            // check if TS page type exists
            /** @var BackendConfigurationManager $configurationManager */
            $backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
            $fullTS = $backendConfigurationManager->getTypoScriptSetup();

            if(isset($fullTS['types.'][$this->typeNum])) {
                // render page title
                $rootline = BackendUtility::BEgetRootLine($pageUid);

                /** @var TSFEUtility $TSFEUtility */
                $TSFEUtility =  GeneralUtility::makeInstance(TSFEUtility::class, $pageUid, $data['sys_language_uid']);

                $siteTitle = $TSFEUtility->getSiteTitle();
                $pageTitleSeparator = $TSFEUtility->getPageTitleSeparator();
                $config = $TSFEUtility->getConfig();

                if($table == 'pages' || $table == 'pages_language_overlay') {
                    PageGenerator::generatePageTitle();
                    $pageTitle = static::getPageRenderer()->getTitle();
                    // get page path
                    $path = $TSFEUtility->getPagePath();
                    // TYPO3 8
                    $urlScheme = is_array($data['url_scheme']) ? $data['url_scheme'][0] : $data['url_scheme'];

                    // check if path is absolute
                    if (strpos($path, '://') !== false) {
                        $path = '';
                    }
                } else {
                    $pageTitle = $TSFEUtility->getFinalTitle($data['title']);
                    $path = '';
                    $urlScheme = 'http://';
                }

                $wizardView->assignMultiple([
                    'config' => $config,
                    'domain' => BackendUtility::firstDomainRecord($rootline),
                    'data' => $data,
                    'pageTitle' => $pageTitle,
                    'pageTitleSeparator' => $pageTitleSeparator,
                    'path' => $path,
                    'siteTitle' => $siteTitle,
                    'urlScheme' => $urlScheme
                ]);
            } else {
                $wizardView->assign('error', 'no_ts');
            }
        } else {
            $wizardView->assign('error', 'no_data');
        }
        return $wizardView->render();
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
     * Returns an instance of LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
