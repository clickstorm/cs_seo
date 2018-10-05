<?php

namespace Clickstorm\CsSeo\Hook;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
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
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * hook to display the evaluation results in the page module
 *
 * Class pageHook
 *
 * @package Clickstorm\CsSeo\Hook
 */
class PageHook
{

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var string $resourcesPath
     */
    protected $resourcesPath;

    public function __construct()
    {
        $this->resourcesPath = 'EXT:cs_seo/Resources/';
    }

    /**
     * Add sys_notes as additional content to the footer of the page module
     *
     * @param array $params
     * @param PageLayoutController $parentObject
     *
     * @return string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function render(array $params, PageLayoutController $parentObject)
    {
        if ($parentObject->MOD_SETTINGS['function'] == 1
            && !$parentObject->modTSconfig['properties']['tx_csseo.']['disable']
        ) {
            $pageInfo = $parentObject->pageinfo;
            if ($this->pageCanBeIndexed($pageInfo)) {
                // template
                $this->loadCss();
                $this->loadJavascript();

                //load partial paths info from typoscript
                $this->view = GeneralUtility::makeInstance(StandaloneView::class);
                $this->view->setFormat('html');
                $this->view->getRequest()->setControllerExtensionName('cs_seo');

                $layoutPaths = [$this->resourcesPath . 'Private/Layouts/'];
                $partialPaths = [$this->resourcesPath . 'Private/Partials/'];

                // load partial paths info from TypoScript
                /** @var ObjectManager $objectManager */
                $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
                /** @var ConfigurationManagerInterface $configurationManager */
                $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
                $tsSetup =
                    $configurationManager->getConfiguration(
                        ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                        'CsSeoHook'
                    );
                $layoutPaths = $tsSetup["view"]["layoutRootPaths"] ?: $layoutPaths;
                $partialPaths = $tsSetup["view"]["partialRootPaths"] ?: $partialPaths;

                $this->view->setLayoutRootPaths($layoutPaths);
                $this->view->setPartialRootPaths($partialPaths);

                $this->view->setTemplatePathAndFilename($this->resourcesPath . 'Private/Templates/PageHook.html'
                );

                $results = $this->getResults($pageInfo, $parentObject->current_sys_language);
                $score = $results['Percentage'];
                unset($results['Percentage']);

                $this->view->assignMultiple(
                    [
                        'score' => $score,
                        'results' => $results,
                        'page' => $parentObject->pageinfo
                    ]
                );

                return $this->view->render();
            }
        }
    }

    /**
     * @param array $page
     *
     * @return bool
     */
    public function pageCanBeIndexed($page)
    {
        $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();
        if (in_array($page['doktype'], $allowedDoktypes) && $page['hidden'] == 0) {
            return true;
        }

        return false;
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
        $cssFiles = [
            'Icons.css',
            'Evaluation.css'
        ];

        $baseUrl = $this->resourcesPath . 'Public/CSS/';

        // Load the wizards css
        foreach ($cssFiles as $cssFile) {
            $this->getPageRenderer()->addCssFile($baseUrl . $cssFile, 'stylesheet', 'all', '', $compress, false);
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

    /**
     * Load the necessary javascript
     *
     * This will only be done when the referenced record is available
     *
     * @return void
     */
    protected function loadJavascript()
    {
        $compress = false;
        $javascriptFiles = [
            'jquery.min.js',
            'jquery.cookie.js',
            'jquery.cs_seo.evaluation.js'
        ];

        // Load the wizards javascript
        $baseUrl = $this->resourcesPath . 'Public/JavaScript/';

        foreach ($javascriptFiles as $javascriptFile) {
            $this->getPageRenderer()->addJsFile(
                $baseUrl . $javascriptFile,
                'text/javascript',
                $compress,
                false,
                '',
                true,
                '|',
                true
            );
        }
    }

    /**
     * @param $pageInfo
     * @param $lang
     *
     * @return array
     */
    protected function getResults($pageInfo, $lang)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_csseo_domain_model_evaluation');
        $results = [];
        $tableName = 'pages';

        if ($lang) {
            $localizedPageInfo = BackendUtility::getRecordLocalization('pages', $pageInfo['uid'], $lang);
            if ($localizedPageInfo[0]) {
                $uidForeign = $localizedPageInfo[0]['uid'];
            } else {
                return [];
            }
        } else {
            $uidForeign = $pageInfo['uid'];
        }

        $res = $queryBuilder->select('results')
            ->from('tx_csseo_domain_model_evaluation')
            ->where(
                $queryBuilder->expr()->eq('uid_foreign',
                    $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableName))
            )
            ->execute();

        while ($row = $res->fetch()) {
            $results = unserialize($row['results']);
        }

        return $results;
    }
}
