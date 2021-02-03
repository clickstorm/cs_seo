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

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\PageTitle\PageTitleProviderManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Google Search Results Preview
 *
 * Class PageTitle
 *
 */
class SnippetPreview extends AbstractNode
{
    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * Render the input field with additional snippet preview
     *
     * @return array
     */
    public function render()
    {
        // first get input element
        $inputField = GeneralUtility::makeInstance(InputTextElement::class, $this->nodeFactory, $this->data);
        $resultArray = $inputField->render();

        // Load necessary JavaScript
        $resultArray['requireJsModules'] = $this->loadJavascript();

        // Load necessary CSS
        $resultArray['stylesheetFiles'] = $this->loadCss();

        // add wizard content
        $resultArray['html'] .= $this->getBodyContent($this->data['databaseRow'], $this->data['tableName']);

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
            'snippetPreview' => [
                'TYPO3/CMS/CsSeo/FormEngine/Element/SnippetPreview' => 'function(SnippetPreview){SnippetPreview.initialize()}'
            ]
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
            'Wizard.css'
        ];
        $baseUrl = 'EXT:cs_seo/Resources/Public/CSS/';
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
    protected function getBodyContent($data, $table)
    {
        // template1
        /** @var StandaloneView $wizardView */
        $wizardView = GeneralUtility::makeInstance(StandaloneView::class);
        $wizardView->setFormat('html');
        $wizardView->setLayoutRootPaths(
            [10 => 'EXT:cs_seo/Resources/Private/Layouts/']
        );
        $wizardView->setTemplatePathAndFilename(
            'EXT:cs_seo/Resources/Private/Templates/Wizard.html'
        );

        if (strpos($data['uid'], 'NEW') === false) {
            // set pageID for TSSetup check
            $pageUid = $data['pid'];

            // use page uid or t3ver_oid is set
            if ($table === 'pages') {
                $pageUid = $data['t3ver_oid'] ?: $data['uid'];
            }

            $_GET['id'] = $pageUid;

            if ($GLOBALS['BE_USER']->workspace >= 0) {
                // render page title
                $rootline = BackendUtility::BEgetRootLine($pageUid);
                $sysLanguageUid = is_array($data['sys_language_uid']) ? (int)current($data['sys_language_uid']) : (int)$data['sys_language_uid'];

                /** @var TSFEUtility $TSFEUtility */
                $TSFEUtility = GeneralUtility::makeInstance(
                    TSFEUtility::class,
                    $pageUid,
                    $sysLanguageUid
                );
                $fallback = [];

                if ($table === 'pages') {
                    $fallback['title'] = 'title';
                    $fallback['uid'] = $data['uid'];
                    $fallback['table'] = $table;
                }

                if (isset($GLOBALS['TSFE'])) {
                    $siteTitle = $TSFEUtility->getSiteTitle();
                    $pageTitleSeparator = $TSFEUtility->getPageTitleSeparator();
                    $config = $TSFEUtility->getConfig();

                    if ($table === 'pages') {
                        $GLOBALS['TSFE']->config['config']['noPageTitle'] = 0;

                        $GLOBALS['TSFE']->generatePageTitle();

                        $pageTitle = static::getPageRenderer()->getTitle();

                        // get page path
                        $path = $TSFEUtility->getPagePath();
                    } else {
                        $fallback = $this->getFallbackData($data);

                        $pageTitle = $TSFEUtility->getFinalTitle($data['title'], $data['title_only']);
                        $path = '';
                    }
                } else {
                    if ($table === 'pages') {
                        $pageTitleProvider = GeneralUtility::makeInstance(PageTitleProviderManager::class);
                        $pageTitle = $pageTitleProvider->getTitle();
                        if (empty($pageTitle)) {
                            $pageTitle = $data['seo_title'] ?: $data['title'];
                        }
                    } else {
                        $fallback = $this->getFallbackData($data);
                        $pageTitle = $data['title'];
                    }
                    $wizardView->assign('error', 'no_tsfe');
                }

                $wizardView->assignMultiple(
                    [
                        'config' => $config,
                        'extConf' => ConfigurationUtility::getEmConfiguration(),
                        'data' => $data,
                        'fallback' => $fallback,
                        'pageTitle' => $pageTitle,
                        'pageTitleSeparator' => $pageTitleSeparator,
                        'path' => $path,
                        'siteTitle' => $siteTitle
                    ]
                );
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

    protected function getFallbackData(array &$data): array
    {
        $tableSettings = ConfigurationUtility::getTableSettings($data['tablenames']);
        if ($tableSettings && is_array($tableSettings['fallback']) && !empty($tableSettings['fallback'])) {
            $fallback = $tableSettings['fallback'];

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['tablenames']);

            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $res = $queryBuilder->select('*')
                ->from($data['tablenames'])
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($data['uid_foreign'], \PDO::PARAM_INT)
                    )
                )
                ->execute()->fetchAll();

            $row = $res[0];

            foreach ($fallback as $seoField => $fallbackField) {
                if (empty($data[$seoField])) {
                    $data[$seoField] = $row[$fallbackField];
                }
            }

            $fallback['uid'] = $data['uid_foreign'];
            $fallback['table'] = $data['tablenames'];

            return $fallback;
        }

        return [];
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
