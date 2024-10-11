<?php

namespace Clickstorm\CsSeo\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Element\TextElement;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

/**
 * Advanced helpers for JSON LD ELement
 *
 * Class PageTitle
 */
class JsonLdElement extends AbstractFormElement
{
    public function __construct(
        private readonly ViewFactoryInterface $viewFactory,
        private readonly PageRenderer $pageRenderer,
    ) {}

    /**
     * Render the input field with additional snippet preview
     *
     * @return array
     */
    public function render(): array
    {
        // first get input element
        $inputField = GeneralUtility::makeInstance(TextElement::class);
        $inputField->setData($this->data);
        $resultArray = $inputField->render();

        // Load necessary JavaScript
        $this->loadJavascript();

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
     */
    protected function loadJavascript(): void
    {
        $this->pageRenderer->loadJavaScriptModule('@clickstorm/cs-seo/FormEngine/Element/JsonLdElement.js');
    }

    /**
     * Load the necessary css
     *
     * This will only be done when the referenced record is available
     */
    protected function loadCss(): array
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
    protected function getBodyContent(array $data, string $table, string $textElement): string
    {
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: [10 => 'EXT:cs_seo/Resources/Private/Templates/Element/'],
            layoutRootPaths: [10 => 'EXT:cs_seo/Resources/Private/Layouts/'],
            request: $GLOBALS['TYPO3_REQUEST'],
        );
        $wizardView = $this->viewFactory->create($viewFactoryData);

        $wizardView->assignMultiple(
            [
                'data' => $data,
                'textElement' => $textElement,
            ]
        );

        return $wizardView->render('JsonLdElement.html');
    }
}
