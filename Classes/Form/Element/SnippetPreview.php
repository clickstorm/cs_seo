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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Page\PageGenerator;

/**
 * Google Search Results Preview
 *
 * Class PageTitle
 *
 * @package Clickstorm\CsSeo\UserFunc
 */
class SnippetPreview extends AbstractNode
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
        $baseUrl = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath('cs_seo')) . 'Resources/Public/CSS/';
        // Load the wizards css
        foreach ($cssFiles as $cssFile) {
            $stylesheetFiles[] = $baseUrl . $cssFile;
        }
        return $stylesheetFiles;
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
            [10 => ExtensionManagementUtility::extPath('cs_seo') . '/Resources/Private/Layouts/']
        );
        $wizardView->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('cs_seo') . 'Resources/Private/Templates/Wizard.html'
        );

        if (strpos($data['uid'], 'NEW') === false) {
            // set pageID for TSSetup check
            $pageUid = ($table == 'pages') ? $data['uid'] : $data['pid'];
            $_GET['id'] = $pageUid;

            // check if TS page type exists
            /** @var BackendConfigurationManager $configurationManager */
            $backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
            $fullTS = $backendConfigurationManager->getTypoScriptSetup();

            if (isset($fullTS['types.'][$this->typeNum]) || $GLOBALS['BE_USER']->workspace > 0) {
                // render page title
                $rootline = BackendUtility::BEgetRootLine($pageUid);

                /** @var TSFEUtility $TSFEUtility */
                $TSFEUtility = GeneralUtility::makeInstance(TSFEUtility::class, $pageUid,
                    (int)$data['sys_language_uid']);
                $fallback = [];

                if (isset($GLOBALS['TSFE'])) {
                    $siteTitle = $TSFEUtility->getSiteTitle();
                    $pageTitleSeparator = $TSFEUtility->getPageTitleSeparator();
                    $config = $TSFEUtility->getConfig();

                    if ($table == 'pages' || $table == 'pages_language_overlay') {
                        $GLOBALS['TSFE']->config['config']['noPageTitle'] = 0;

                        /** @TODO remove in 10 */
                        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
                            $GLOBALS['TSFE']->generatePageTitle();
                        } else {
                            PageGenerator::generatePageTitle();
                        }

                        $pageTitle = static::getPageRenderer()->getTitle();
                        // get page path
                        $path = $TSFEUtility->getPagePath();
                        // TYPO3 8
                        $urlScheme = is_array($data['url_scheme']) ? $data['url_scheme'][0] : $data['url_scheme'];

                        // check if path is absolute
                        if (strpos($path, '://') !== false) {
                            $pathData = parse_url($path);
                            if (isset($pathData['path']) && !empty($pathData['path'])) {
                                $path = ltrim($pathData['path'], '/');
                            } else {
                                $path = '';
                            }
                        }
                        $fallback['title'] = 'title';
                        $fallback['uid'] = $data['uid'];
                        $fallback['table'] = $table;
                    } else {
                        $pageTSConfig = BackendUtility::getPagesTSconfig($pageUid);

                        // handle fallback
                        if (isset($pageTSConfig['tx_csseo.'])) {
                            foreach ($pageTSConfig['tx_csseo.'] as $key => $settings) {
                                if (is_string($settings)) {
                                    if ($settings == $data['tablenames']
                                        && isset(
                                            $pageTSConfig['tx_csseo.'][$key
                                            . '.']['fallback.']
                                        )
                                    ) {
                                        $fallback = $pageTSConfig['tx_csseo.'][$key . '.']['fallback.'];
                                        break;
                                    }
                                }
                            }
                        }

                        if ($fallback) {
                            /** @var QueryBuilder $queryBuilder */
                            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['tablenames']);

                            $queryBuilder
                                ->getRestrictions()
                                ->removeAll();

                            $res = $queryBuilder->select('*')
                                ->from($data['tablenames'])
                                ->where(
                                    $queryBuilder->expr()->eq('uid',
                                        $queryBuilder->createNamedParameter($data['uid_foreign'], \PDO::PARAM_INT))
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
                        }

                        $pageTitle = $TSFEUtility->getFinalTitle($data['title'], $data['title_only']);
                        $path = '';
                        $urlScheme = 'http://';
                    }

                    $wizardView->assignMultiple(
                        [
                            'config' => $config,
                            'extConf' => ConfigurationUtility::getEmConfiguration(),
                            'data' => $data,
                            'domain' => BackendUtility::firstDomainRecord($rootline),
                            'fallback' => $fallback,
                            'pageTitle' => $pageTitle,
                            'pageTitleSeparator' => $pageTitleSeparator,
                            'path' => $path,
                            'siteTitle' => $siteTitle,
                            'urlScheme' => $urlScheme
                        ]
                    );
                } else {
                    $wizardView->assign('error', 'no_tsfe');
                }
            } else {
                $wizardView->assign('error', 'no_ts');
            }
        } else {
            $wizardView->assign('error', 'no_data');
        }

        return $wizardView->render();
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
     * Returns an instance of LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
