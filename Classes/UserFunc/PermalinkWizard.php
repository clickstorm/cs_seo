<?php
namespace Clickstorm\CsSeo\UserFunc;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Marc Hirdes <Marc_Hirdes@gmx.de>, clickstorm GmbH
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

use Clickstorm\CsSeo\Utility\TSFE;
use Clickstorm\CsSeo\UserFunc\PageTitle;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageGenerator;

/**
 * Google Search Results Preview
 */
class PermalinkWizard
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
        $GLOBALS['SOBE'] = $this;
        // Define the document template object
        $this->doc = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
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
        return '';
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
        $javascriptFiles = [
            'jquery.seourl.js',
            'jquery.cs_seo.path.js'
        ];
        // Load jquery
        $this->getPageRenderer()->loadJquery();
        // Load the wizards javascript
        $baseUrl = ExtensionManagementUtility::extRelPath('cs_seo') . 'Resources/Public/JavaScript/';
        foreach ($javascriptFiles as $javascriptFile) {
            $this->getPageRenderer()->addJsFile($baseUrl . $javascriptFile, 'text/javascript', $compress, false, '', false, '|', true);
        }
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
