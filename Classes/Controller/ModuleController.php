<?php

namespace Clickstorm\CsSeo\Controller;

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
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * Class ModuleController
 *
 * @package Clickstorm\CsSeo\Controller
 */
class ModuleController extends ActionController
{

    /**
     * @var string prefix for session
     */
    const SESSION_PREFIX = 'tx_csseo_';
    const MOD_NAME = 'web_CsSeoMod1';

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pageRepository;

    /**
     * @var \Clickstorm\CsSeo\Domain\Repository\EvaluationRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $evaluationRepository;

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $modParams = ['action' => '', 'id' => 0, 'lang' => 0, 'depth' => 1, 'table' => 'pages', 'record' => 0];

    /**
     * @var array
     */
    protected $modSharedTSconfig = [];

    /**
     * @var array
     */
    protected $languages = [];

    /**
     * @var bool
     */
    protected $showResults = false;

    /**
     * @var \Clickstorm\CsSeo\Utility\TSFEUtility
     */
    protected $TSFEUtility;

    /**
     * field names to show in current action
     *
     * @var array $fieldNames
     */
    protected $fieldNames = [];

    /**
     * field names with an image relation
     * @var array
     */
    protected $imageFieldNames = ['tx_csseo_og_image', 'tx_csseo_tw_image'];

    /**
     *
     * @var array
     */
    protected $jsFiles = [];

    /**
     *
     * @var array
     */
    protected $requireJsModules = [];

    /**
     *
     * @var array
     */
    protected $cssFiles = [];

    /**
     * available Actions in Menu
     *
     * @var array
     */
    protected $menuSetup = [
        'pageMeta',
        'pageIndex',
        'pageOpenGraph',
        'pageTwitterCards',
        'pageResults',
        'pageEvaluation'
    ];

    /**
     * Show SEO fields
     */
    public function pageMetaAction()
    {
        $this->fieldNames = ['title', 'seo_title', 'tx_csseo_title_only', 'description'];

        // preview settings
        $previewSettings = [];
        $previewSettings['siteTitle'] = $this->TSFEUtility->getSiteTitle();
        $previewSettings['pageTitleFirst'] = $this->TSFEUtility->getPageTitleFirst();
        $previewSettings['pageTitleSeparator'] = $this->TSFEUtility->getPageTitleSeparator();

        if ($previewSettings['pageTitleFirst']) {
            $previewSettings['siteTitle'] = $previewSettings['pageTitleSeparator'] . $previewSettings['siteTitle'];
        } else {
            $previewSettings['siteTitle'] .= $previewSettings['pageTitleSeparator'];
        }

        $this->view->assign('previewSettings', json_encode($previewSettings));

        $this->processFields();

        return $this->wrapModuleTemplate();
    }

    /**
     * process all fields for the UI grid JSON
     *
     */
    protected function processFields()
    {
        // add grid JS and CSS files
        $this->assignGridResources();

        // build the rows
        if ($this->modParams['id'] == 0) {
            return;
        }

        // build the columns
        $columnDefs = [];
        foreach ($this->fieldNames as $fieldName) {
            $columnDefs[] = $this->getColumnDefinition($fieldName);
        }

        // fetch the rows
        if ($this->modParams['lang'] > 0) {
            $this->pageRepository->sys_language_uid = $this->modParams['lang'];
            $columnDefs[] = $this->getColumnDefinition('sys_language_uid');
        }

        $page = $this->pageRepository->getPage($this->modParams['id']);
        $rowEntries = $this->getPageTree($page, $this->modParams['depth']);

        $this->view->assignMultiple(
            [
                'pageJSON' => $this->buildGridJSON($rowEntries, $columnDefs),
                'depth' => $this->modParams['depth'],
                'lang' => $this->modParams['lang'],
                'languages' => $this->languages,
                'action' => $this->modParams['action']
            ]
        );
    }

    protected function assignGridResources()
    {
        $this->jsFiles = [
            'Module/lib/angular.js',
            'Module/lib/angular-touch.min.js',
            'Module/lib/angular-animate.min.js',
            'Module/lib/ui-bootstrap-custom-tpls-1.3.3.min.js',
            'Module/lib/ui-grid.min.js',
            'Module/app.js',
            'Module/app.js',
            'Module/controllers/CsSeoController.js',
            'Module/services/previewTitleFactory.js'
        ];

        $this->cssFiles = [
            'Lib/bootstrap/css/bootstrap.min.css',
            'Lib/ui-grid/ui-grid.min.css',
            'Wizard.css',
            'Module.css'
        ];
    }

    /**
     * get the UI grid column definition for the current field
     *
     * @param $fieldName
     *
     * @return mixed
     */
    protected function getColumnDefinition($fieldName)
    {
        $columnDef = ['field' => $fieldName];
        if ($fieldName != 'sys_language_uid' && $fieldName != 'results') {
            $columnDef['displayName'] =
                $this->getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$fieldName]['label']);
            switch ($GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['type']) {
                case 'check':
                    $columnDef['type'] = 'boolean';
                    $columnDef['width'] = 100;
                    $columnDef['cellTemplate'] =
                        '<div class="ui-grid-cell-contents ng-binding ng-scope text-center"><span class="glyphicon glyphicon-{{row.entity[col.field] == true ? \'ok\' : \'remove\'}}"></span></div>';
                    $columnDef['editableCellTemplate'] =
                        '<div><form name="inputForm" class="text-center"><input type="checkbox" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-click="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
                    $columnDef['enableFiltering'] = false;
                    break;
                case 'inline':
                    $columnDef['type'] = 'object';
                    break;
                case 'text':
                    $columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
                    $columnDef['editableCellTemplate'] =
                        '<div><form name="inputForm"><textarea class="form-control" ng-maxlength="'
                        . $columnDef['max']
                        . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
                    break;
                default:
                    $columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'];
                    $columnDef['editableCellTemplate'] =
                        '<div><form name="inputForm" ng-model="form"><input type="INPUT_TYPE" class="form-control" ng-maxlength="'
                        . $columnDef['max']
                        . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
            }
        }

        switch ($fieldName) {
            case 'title':
                $columnDef['cellTemplate'] =
                    '<div class="ui-grid-cell-contents ng-binding ng-scope"><span ng-repeat="i in grid.appScope.rangeArray | limitTo: row.entity.level">&nbsp;&nbsp;</span>{{row.entity.title}}</div>';
                break;
            case 'seo_title':
                $columnDef['min'] = 35;
                break;
            case 'description':
                $columnDef['min'] = 120;
                break;
            case 'keyword':
                $columnDef['nl2separator'] = true;
                break;
            case 'sys_language_uid':
                $columnDef['displayName'] =
                    $this->getLanguageService()->sL(
                        $GLOBALS['TCA']['pages']['columns'][$fieldName]['label']
                    );
                $columnDef['width'] = 100;
                $columnDef['type'] = 'object';
                $columnDef['enableFiltering'] = false;
                break;
            case 'results':
                $columnDef['displayName'] =
                    $this->getLanguageService()->sL(
                        $GLOBALS['TCA']['tx_csseo_domain_model_evaluation']['columns'][$fieldName]['label']
                    );
                $columnDef['type'] = 'object';
        }

        return json_encode($columnDef);
    }

    /**
     * Returns the language service
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * recursive function for building a page array
     *
     * @param array $page the current page
     * @param int $depth the current depth
     * @param array $pages contains all pages so far
     * @param int $level the tree level required for the UI grid
     *
     * @return array
     */
    protected function getPageTree($page, $depth, $pages = [], $level = 0)
    {
        // default query settings
        $fields = '*';
        $sortField = 'sorting';
        $table = 'pages';
        $uid = $page['uid'];

        // decrease the depth
        $depth--;

        // add the current language value
        if ($this->modParams['lang'] > 0) {
            if ($page['_PAGES_OVERLAY_UID']) {
                $uid = $page['_PAGES_OVERLAY_UID'];
            }

            $page['sys_language_uid'] = $this->languages[$page['_PAGES_OVERLAY_LANGUAGE'] ?: 0];
        }

        // process social media image fields
        foreach ($this->imageFieldNames as $imageFieldName) {
            if (in_array($imageFieldName, $this->fieldNames)) {
                $image = '';
                if ($page[$imageFieldName]) {
                    $imageFile = DatabaseUtility::getFile($table, $imageFieldName, $uid);
                    if ($imageFile) {
                        $image = $imageFile->getPublicUrl();
                    }
                }
                $page[$imageFieldName] = $image;
            }
        }

        if ($this->showResults) {
            $results = $this->getResults($page);
            $page['results'] = $results['Percentage']['count'];
        }

        $page['level'] = $level;

        // add the page to the pages array
        $pages[] = &$page;

        // fetch subpages and set the treelevel
        if ($depth > 0) {
            $subPages = $this->pageRepository->getMenu($page['uid'], $fields, $sortField);
            if (count($subPages) > 0) {
                $page['$$treeLevel'] = $level;
                $level++;
                foreach ($subPages as &$subPage) {
                    $pages = $this->getPageTree($subPage, $depth, $pages, $level);
                }
            }
        }

        return $pages;
    }

    /**
     * return evaluation results of a specific page
     *
     * @param $record
     * @param $table
     *
     * @return array
     */
    protected function getResults($record, $table = '')
    {
        $results = [];
        $evaluation = $this->getEvaluation($record, $table);
        if ($evaluation) {
            $results = $evaluation->getResults();
        }

        return $results;
    }

    protected function getEvaluation($record, $table = '')
    {
        if ($table) {
            $evaluation = $this->evaluationRepository->findByUidForeignAndTableName($record, $table);
        } else {
            if (isset($record['_PAGES_OVERLAY_LANGUAGE'])) {
                $evaluation =
                    $this->evaluationRepository->findByUidForeignAndTableName(
                        $record['_PAGES_OVERLAY_UID'],
                        'pages'
                    );
            } else {
                $evaluation = $this->evaluationRepository->findByUidForeignAndTableName((int)$record['uid'], 'pages');
            }
        }

        return $evaluation;
    }

    /**
     * returns the final JSON incl. settings for the UI Grid
     *
     * @param $rowEntries
     * @param $columnDefs
     *
     * @return string
     */
    protected function buildGridJSON($rowEntries, $columnDefs)
    {
        $doktypes = '[' . implode(',', ConfigurationUtility::getEvaluationDoktypes()) . ']';

        return '
			{
				data:' . json_encode($rowEntries) . ',
				columnDefs: [' . implode(',', $columnDefs) . '],
				enableSorting: true,
				showTreeExpandNoChildren: false,
				enableGridMenu: true,
				expandAll: true,
				enableFiltering: true,
				doktypes: ' . $doktypes . ',
				i18n: \'' . $this->modParams['lang'] . '\',
				cellEditableCondition: function($scope) {
					return (' . $doktypes . '.indexOf(parseInt($scope.row.entity.doktype)) > -1)
				}
			}
		';
    }

    protected function wrapModuleTemplate()
    {
        // Prepare module setup
        $moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $moduleTemplate->setContent($this->view->render());

        foreach ($this->jsFiles as $jsFile) {
            $moduleTemplate->getPageRenderer()->addJsFile('EXT:cs_seo/Resources/Public/JavaScript/' . $jsFile);
        }

        foreach ($this->requireJsModules as $requireJsModule) {
            $moduleTemplate->getPageRenderer()->loadRequireJsModule($requireJsModule);
        }

        foreach ($this->cssFiles as $cssFile) {
            $moduleTemplate->getPageRenderer()->addCssFile('EXT:cs_seo/Resources/Public/CSS/' . $cssFile);
        }

        // Shortcut in doc header
        $shortcutButton = $moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $shortcutButton->setModuleName('web_CsSeoMod1')
            ->setDisplayName($this->getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:mlang_labels_tablabel'
            ))
            ->setSetVariables(['tree']);
        $moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($shortcutButton);

        // The page will show only if there is a valid page and if this page
        // may be viewed by the user
        $pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        if ($pageinfo) {
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($pageinfo);
        }

        // Main drop down in doc header
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('action');
        foreach ($this->menuSetup as $menuKey) {
            $menuItem = $menu->makeMenuItem();
            /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
            $menuItem->setHref((string)$uriBuilder->buildUriFromRoute(
                'web_CsSeoMod1',
                ['tx_csseo_web_csseomod1' => ['action' => $menuKey, 'Controller' => 'Module']]))
                ->setTitle($this->getLanguageService()->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:layouts.module.action.' . $menuKey
                ));

            if ($this->actionMethodName === $menuKey . 'Action') {
                $menuItem->setActive(true);
            }
            $menu->addMenuItem($menuItem);
        }
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        return $moduleTemplate->renderContent();
    }

    /**
     * Show Index properties
     */
    public function pageIndexAction()
    {
        $this->fieldNames = ['title', 'canonical_link', 'no_index', 'no_follow', 'no_search'];

        $this->processFields();

        return $this->wrapModuleTemplate();
    }

    /**
     * Show Open Graph properties
     */
    public function pageOpenGraphAction()
    {
        $this->fieldNames = ['title', 'og_title', 'og_description', 'og_image'];

        $this->processFields();

        return $this->wrapModuleTemplate();
    }

    /**
     * Show Twitter Cards properties
     */
    public function pageTwitterCardsAction()
    {
        $this->fieldNames =
            [
                'title',
                'twitter_title',
                'twitter_description',
                'tx_csseo_tw_creator',
                'tx_csseo_tw_site',
                'twitter_image'
            ];

        $this->processFields();

        return $this->wrapModuleTemplate();
    }

    /**
     * Show page evaluation results
     */
    public function pageResultsAction()
    {
        $this->fieldNames = ['title', 'tx_csseo_keyword', 'results'];
        $this->showResults = true;
        $this->processFields();

        return $this->wrapModuleTemplate();
    }

    /**
     * Show page evaluation results
     */
    public function pageEvaluationAction()
    {
        $page = $this->pageRepository->getPage($this->modParams['id']);
        $extKey = 'cs_seo';
        $tables = [
            'pages' => LocalizationUtility::translate($GLOBALS['TCA']['pages']['ctrl']['title'], $extKey)
        ];

        $tablesToExtend = ConfigurationUtility::getTablesToExtend();

        foreach ($tablesToExtend as $tableToExtend) {
            $tableSettings = ConfigurationUtility::getTableSettings($tableToExtend);
            if ($tableSettings['evaluation.'] && $tableSettings['evaluation.']['detailPid']) {
                $tables[$tableToExtend] =
                    LocalizationUtility::translate($GLOBALS['TCA'][$tableToExtend]['ctrl']['title'], $extKey);
            }
        }

        $table = $this->modParams['table'];
        if ($table && $table != 'pages') {
            $records = DatabaseUtility::getRecords($table);
            $record = $this->modParams['record'];
            if ($record) {
                $evaluation = $this->getEvaluation($record, $table);
            }

            $this->view->assignMultiple(
                [
                    'record' => $record,
                    'records' => $records
                ]
            );
        } else {
            $table = 'pages';
            $languages = [];

            // get available languages
            $pageOverlays = DatabaseUtility::getPageLanguageOverlays($page['uid']);
            $languages[0] = $this->languages[0];

            if ($pageOverlays) {
                $languagesUids = array_keys($pageOverlays);
                foreach ($this->languages as $langUid => $languageLabel) {
                    if ($langUid > 0 && in_array($langUid, $languagesUids)) {
                        $languages[$langUid] = $languageLabel;
                    }
                }
            }

            // get page
            $languageParam = $this->modParams['lang'];
            if ($languageParam > 0) {
                $page = $this->pageRepository->getPageOverlay($page, $languageParam);
            }
            $evaluation = $this->getEvaluation($page);

            $langResult = $page['_PAGES_OVERLAY_LANGUAGE'] ?: 0;
            $this->view->assignMultiple(
                [
                    'lang' => $languageParam,
                    'languages' => $languages,
                    'langDisplay' => $this->languages[$langResult]
                ]
            );
        }

        if (isset($evaluation)) {
            $results = $evaluation->getResults();
            $score = $results['Percentage'];
            unset($results['Percentage']);
            $this->view->assignMultiple(
                [
                    'evaluation' => $evaluation,
                    'score' => $score,
                    'results' => $results
                ]
            );
        }

        $emConf = ConfigurationUtility::getEmConfiguration();

        $this->view->assignMultiple(
            [
                'emConf' => $emConf,
                'page' => $page,
                'tables' => $tables,
                'table' => $table
            ]
        );

        $this->requireJsModules = [
            'TYPO3/CMS/CsSeo/Evaluation'
        ];

        $this->jsFiles = [
            'jquery.min.js',
            'select2.js'
        ];

        $this->cssFiles = [
            'Icons.css',
            'Lib/select2.css',
            'Evaluation.css'
        ];

        return $this->wrapModuleTemplate();
    }

    /**
     * Renders the menu so that it can be returned as response to an AJAX call
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {

        // get parameter
        $postdata = file_get_contents("php://input");
        $attr = json_decode($postdata, true);

        // prepare data array
        $tableName = 'pages';
        $uid = $attr['entry']['uid'];
        $field = $attr['field'];

        // check for language overlay
        if ($attr['entry']['_PAGES_OVERLAY'] && isset($GLOBALS['TCA']['pages']['columns'][$field])) {
            $uid = $attr['entry']['_PAGES_OVERLAY_UID'];
        }

        // update map
        $data[$tableName][$uid][$field] = $attr['value'];

        // update data
        $dataHandler = $this->getDataHandler();
        $dataHandler->datamap = $data;
        $dataHandler->process_datamap();
        if (!empty($dataHandler->errorLog)) {
            $response->getBody()->write('Error: ' . implode(',', $dataHandler->errorLog));
        }

        return $response;
    }

    /**
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected function getDataHandler()
    {
        if (!isset($this->dataHandler)) {
            $this->dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
            $this->dataHandler->start(null, null);
        }

        return $this->dataHandler;
    }

    /**
     * Initialize action
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function initializeAction()
    {
        // initialize page/be_user TSconfig settings
        $this->id = (int)GeneralUtility::_GP('id');
        $this->modSharedTSconfig = BackendUtility::getPagesTSconfig($this->id)['mod.']['SHARED.'] ?? [];

        // initialize settings of the module
        $this->initializeModParams();
        if (!$this->request->hasArgument('action') && $this->modParams['action']) {
            $this->request->setArgument('action', $this->modParams['action']);
            $this->forward($this->modParams['action']);
        }

        // get languages
        $this->languages = $this->getLanguages();

        $this->TSFEUtility = GeneralUtility::makeInstance(TSFEUtility::class, $this->id, $this->modParams['lang']);
    }

    /**
     * initialize the settings for the current view
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializeModParams()
    {
        foreach ($this->modParams as $name => $value) {
            $this->modParams[$name] = ((int)GeneralUtility::_GP($name) > 0)
                ? (int)GeneralUtility::_GP($name)
                : $this->getBackendUser()->getSessionData(self::SESSION_PREFIX . $name);

            if ($this->request->hasArgument($name)) {
                $arg = $this->request->getArgument($name);
                $this->modParams[$name] = ($name == 'action' || $name == 'table') ? $arg : (int)$arg;
            }
            $this->getBackendUser()->setAndSaveSessionData(self::SESSION_PREFIX . $name, $this->modParams[$name]);
        }
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns a SQL query for selecting sys_language records.
     *
     * @return string Return query string.
     */
    protected function getLanguages()
    {
        $languages[0] = 'Default';

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');

        $res = $queryBuilder->select('*')
            ->from('sys_language')
            ->orderBy('title')
            ->execute();

        while ($lRow = $res->fetch()) {
            if ($this->getBackendUser()->checkLanguageAccess($lRow['uid'])) {
                $languages[$lRow['uid']] = $lRow['hidden'] ? '(' . $lRow['title'] . ')' : $lRow['title'];
            }
        }
        // Setting alternative default label:
        if ($this->modSharedTSconfig['properties']['defaultLanguageLabel']) {
            $languages[0] = $this->modSharedTSconfig['properties']['defaultLanguageLabel'];
        }

        return $languages;
    }
}
