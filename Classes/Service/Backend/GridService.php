<?php

namespace Clickstorm\CsSeo\Service\Backend;

use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to build and process the grid view in the backend modules
 */
class GridService
{
    protected array $modParams = [];

    protected array $fieldNames = [];

    protected array $languages = [];

    protected bool $showResults = false;

    protected int $pageUid = 0;

    protected array $imageFieldNames = ['tx_csseo_og_image', 'tx_csseo_tw_image'];

    protected ?PageRepository $pageRepository = null;

    protected ?EvaluationService $evaluationService = null;

    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    public function injectEvaluationService(EvaluationService $evaluationService): void
    {
        $this->evaluationService = $evaluationService;
    }

    public function setShowResults(bool $showResults): void
    {
        $this->showResults = $showResults;
    }

    public function setFieldNames(array $fieldNames): void
    {
        $this->fieldNames = $fieldNames;
    }

    public function setModParams(array $modParams): void
    {
        $this->modParams = $modParams;
        $this->pageUid = (int)$this->modParams['id'];
        $this->languages = DatabaseUtility::getLanguagesInBackend($this->pageUid); // get languages
    }

    public function getJsFiles(): array
    {
        return [
            'Module/lib/angular.js',
            'Module/lib/angular-touch.min.js',
            'Module/lib/angular-animate.min.js',
            'Module/lib/ui-bootstrap-custom-tpls-1.3.3.min.js',
            'Module/lib/ui-grid.min.js',
            'Module/app.js',
            'Module/app.js',
            'Module/controllers/CsSeoController.js',
            'Module/services/previewTitleFactory.js',
        ];
    }

    public function getCssFiles(): array
    {
        return [
            'Lib/ui-grid/ui-grid.min.css',
            'Wizard.css',
            'Module.css',
        ];
    }

    /**
     * process all fields for the UI grid JSON
     */
    public function processFields(): array
    {
        $context = GeneralUtility::makeInstance(Context::class);

        // build the rows
        if ($this->pageUid === 0) {
            return [];
        }

        // build the columns
        $columnDefs = [];
        foreach ($this->fieldNames as $fieldName) {
            $columnDefs[] = $this->getColumnDefinition($fieldName);
        }

        // fetch the rows
        if ($this->modParams['lang'] > 0) {
            /** @var LanguageAspect $languageAspect */
            $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $this->modParams['lang']);
            $context->setAspect('language', $languageAspect);
            $columnDefs[] = $this->getColumnDefinition('sys_language_uid');
        }

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, $context);

        $page = $this->pageRepository->getPage($this->pageUid, true);
        $rowEntries = $this->getPageTree($page, (int)$this->modParams['depth']);

        return [
            'pageJSON' => $this->buildGridJSON($rowEntries, $columnDefs),
            'depth' => $this->modParams['depth'],
            'lang' => $this->modParams['lang'],
            'languages' => $this->languages,
            'action' => $this->modParams['action'],
        ];
    }

    /**
     * get the UI grid column definition for the current field
     * @throws \JsonException
     */
    public function getColumnDefinition(string $fieldName): string
    {
        $columnDef = ['field' => $fieldName];
        if ($fieldName !== 'sys_language_uid' && $fieldName !== 'results') {
            $columnDef['displayName'] =
                GlobalsUtility::getLanguageService()->sL($GLOBALS['TCA']['pages']['columns'][$fieldName]['label']);
            switch ($GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['type']) {
                case 'check':
                    $columnDef['type'] = 'boolean';
                    $columnDef['width'] = 100;
                    $columnDef['cellTemplate'] =
                        '<div class="ui-grid-cell-contents ng-binding ng-scope text-center">{{row.entity[col.field] == true ? \'☑\' : \'☐\'}}</span></div>';
                    $columnDef['editableCellTemplate'] =
                        '<div><form name="inputForm" class="text-center"><input type="checkbox" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-click="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
                    $columnDef['enableFiltering'] = false;
                    break;
                case 'inline':
                    $columnDef['type'] = 'object';
                    break;
                case 'text':
                    $columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'] ?? '';
                    $columnDef['editableCellTemplate'] =
                        '<div><form name="inputForm"><textarea class="form-control" ng-maxlength="'
                        . $columnDef['max']
                        . '" ui-grid-editor ng-model="MODEL_COL_FIELD" ng-keyup="grid.appScope.currentValue = MODEL_COL_FIELD"></form></div>';
                    break;
                default:
                    $columnDef['max'] = $GLOBALS['TCA']['pages']['columns'][$fieldName]['config']['max'] ?? '';
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
                    GlobalsUtility::getLanguageService()->sL(
                        $GLOBALS['TCA']['pages']['columns'][$fieldName]['label']
                    );
                $columnDef['width'] = 100;
                $columnDef['type'] = 'object';
                $columnDef['enableFiltering'] = false;
                break;
            case 'results':
                $columnDef['displayName'] =
                    GlobalsUtility::getLanguageService()->sL(
                        $GLOBALS['TCA']['tx_csseo_domain_model_evaluation']['columns'][$fieldName]['label']
                    );
                $columnDef['type'] = 'object';
        }

        return json_encode($columnDef, JSON_THROW_ON_ERROR);
    }

    /**
     * recursive function for building a page array
     *
     * @param array $page the current page
     * @param int $depth the current depth
     * @param array $pages contains all pages so far
     * @param int $level the tree level required for the UI grid
     */
    protected function getPageTree(array $page, int $depth, array $pages = [], int $level = 0): array
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
            if (!empty($page['_PAGES_OVERLAY_UID'])) {
                $uid = $page['_PAGES_OVERLAY_UID'];
            }

            $page['sys_language_uid'] = $this->languages[$page['_PAGES_OVERLAY_LANGUAGE'] ?? 0];
        }

        // process social media image fields
        foreach ($this->imageFieldNames as $imageFieldName) {
            if (in_array($imageFieldName, $this->fieldNames, true)) {
                $image = '';
                if ($page[$imageFieldName]) {
                    $imageFile = DatabaseUtility::getFile($table, $imageFieldName, $uid);
                    if ($imageFile !== null) {
                        $image = $imageFile->getPublicUrl();
                    }
                }
                $page[$imageFieldName] = $image;
            }
        }

        if ($this->showResults) {
            $results = $this->evaluationService->getResults($page);
            $page['results'] = $results['Percentage']['count'] ?? 0;
        }

        $page['level'] = $level;

        // add the page to the pages array
        $pages[] = &$page;

        // fetch subpages and set the treelevel
        if ($depth > 0) {
            $subPages = $this->pageRepository->getMenu($page['uid'], $fields, $sortField);
            if ($subPages !== []) {
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
     * returns the final JSON incl. settings for the UI Grid
     */
    protected function buildGridJSON(array $rowEntries, array $columnDefs): string
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
				i18n: \'' . GlobalsUtility::getBackendUser()->user['lang'] . '\',
				cellEditableCondition: function($scope) {
					return (' . $doktypes . '.indexOf(parseInt($scope.row.entity.doktype)) > -1)
				}
			}
		';
    }
}
