<?php
namespace Clickstorm\CsSeo\UserFunc;

/***************************************************************
 *
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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Set the RealURL path segment if empty
 *
 * Class PageTitle
 *
 * @package Clickstorm\CsSeo\UserFunc
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
        $baseUrl = ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Public/JavaScript/';
        foreach ($javascriptFiles as $javascriptFile) {
            $this->getPageRenderer()->addJsFile(
                $baseUrl . $javascriptFile,
                'text/javascript',
                $compress,
                false,
                '',
                false,
                '|',
                true
            );
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
