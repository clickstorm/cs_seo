<?php

namespace Clickstorm\CsSeo\Form\Element;

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

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\Element\TextElement;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Advanced helpers for JSON LD ELement
 *
 * Class PageTitle
 */
class JsonLdElement extends AbstractNode
{
    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var int
     */
    protected $typeNum = 654;

    /**
     * Render the input field with additional snippet preview
     *
     * @return array
     */
    public function render()
    {
        // first get input element
        $inputField = GeneralUtility::makeInstance(TextElement::class, $this->nodeFactory, $this->data);
        $resultArray = $inputField->render();

        // Load necessary JavaScript
        $resultArray['requireJsModules'] = $this->loadJavascript();

        // Load necessary CSS
        $resultArray['stylesheetFiles'] = $this->loadCss();

        // add wizard content
        $resultArray['html'] = $this->getBodyContent($this->data['databaseRow'], $this->data['tableName'], $resultArray['html']);

        return $resultArray;
    }

    /**
     * Load the necessary javascript
     *
     * This will only be done when the referenced record is available
     *
     * @return array
     */
    protected function loadJavascript()
    {
        return [
            'jsonLdElement' => [
                'TYPO3/CMS/CsSeo/FormEngine/Element/JsonLdElement' => 'function(jsonLdElement){jsonLdElement.initialize()}',
            ],
        ];
    }

    /**
     * Load the necessary css
     *
     * This will only be done when the referenced record is available
     *
     * @return array
     */
    protected function loadCss()
    {
        $stylesheetFiles = [];
        $cssFiles = [
            'JsonLd.css',
        ];
        $baseUrl = 'EXT:cs_seo/Resources/Public/Css/';
        // Load the wizards css
        foreach ($cssFiles as $cssFile) {
            $stylesheetFiles[] = $baseUrl . $cssFile;
        }

        return $stylesheetFiles;
    }

    /**
     * Generate the body content
     *
     * If there is an error, no reference to a record, a Flash Message will be
     * displayed
     *
     * @return string The body content
     */
    protected function getBodyContent($data, $table, $textElement)
    {
        // template1
        /** @var StandaloneView $wizardView */
        $wizardView = GeneralUtility::makeInstance(StandaloneView::class);
        $wizardView->setFormat('html');
        $wizardView->setLayoutRootPaths(
            [10 => 'EXT:cs_seo/Resources/Private/Layouts/']
        );
        $wizardView->setTemplatePathAndFilename(
            'EXT:cs_seo/Resources/Private/Templates/Element/JsonLdElement.html'
        );

        $wizardView->assignMultiple(
            [
                'data' => $data,
                'textElement' => $textElement,
            ]
        );

        return $wizardView->render();
    }
}
