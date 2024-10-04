<?php

namespace Clickstorm\CsSeo\Controller;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use Clickstorm\CsSeo\Service\Backend\GridService;
use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class ModuleController
 */
class ModuleWebController extends AbstractModuleController
{
    protected ?PageRepository $pageRepository = null;

    protected ?EvaluationService $evaluationService = null;

    protected ?GridService $gridService;

    public static array $menuActions = [
        'pageMeta',
        'pageIndex',
        'pageOpenGraph',
        'pageTwitterCards',
        'pageStructuredData',
        'pageResults',
        'pageEvaluation',
    ];

    public function injectEvaluationService(EvaluationService $evaluationService): void
    {
        $this->evaluationService = $evaluationService;
    }

    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * Show SEO fields
     */
    public function pageMetaAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageMeta';
        $fieldNames = ['title', 'seo_title', 'tx_csseo_title_only', 'description'];

        // get title and settings from TypoScript
        $tsfeUtility = GeneralUtility::makeInstance(TSFEUtility::class, $this->recordId, $this->modParams['lang']);
        $this->view->assign('previewSettings', json_encode($tsfeUtility->getPreviewSettings()));

        return $this->htmlResponse($this->generateGridView($fieldNames));
    }

    protected function generateGridView(array $fieldNames, bool $showResults = false): string
    {
        $gridService = GeneralUtility::makeInstance(GridService::class);

        $gridService->setModParams($this->modParams);
        $gridService->setFieldNames($fieldNames);
        $gridService->setShowResults($showResults);

        $this->cssFiles = $gridService->getCssFiles();
        $this->jsFiles = $gridService->getJsFiles();

        $this->view->assignMultiple($gridService->processFields());

        return $this->wrapModuleTemplate();
    }

    /**
     * Show Index properties
     */
    public function pageIndexAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageIndex';
        return $this->htmlResponse($this->generateGridView(['title', 'canonical_link', 'no_index', 'no_follow', 'no_search']));
    }

    /**
     * Show Open Graph properties
     */
    public function pageOpenGraphAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageOpenGraph';
        return $this->htmlResponse($this->generateGridView(['title', 'og_title', 'og_description', 'og_image']));
    }

    /**
     * Show Structure Data properties
     */
    public function pageStructuredDataAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageStructuredData';
        return $this->htmlResponse($this->generateGridView(['title', 'tx_csseo_json_ld']));
    }

    /**
     * Show Twitter Cards properties
     */
    public function pageTwitterCardsAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageTwitterCards';
        return $this->htmlResponse($this->generateGridView([
            'title',
            'twitter_title',
            'twitter_description',
            'tx_csseo_tw_creator',
            'tx_csseo_tw_site',
            'twitter_image',
        ]));
    }

    /**
     * Show page evaluation results
     */
    public function pageResultsAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageResults';
        return $this->htmlResponse($this->generateGridView(['title', 'tx_csseo_keyword', 'results'], true));
    }

    /**
     * Show page evaluation results
     */
    public function pageEvaluationAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleWeb/PageEvaluation';
        $page = $this->pageRepository->getPage($this->modParams['id'], true);
        $evaluationUid = 0;
        $extKey = 'cs_seo';
        $tables = [
            'pages' => LocalizationUtility::translate($GLOBALS['TCA']['pages']['ctrl']['title'], $extKey),
        ];

        $tablesToExtend = ConfigurationUtility::getTablesToExtend();

        foreach ($tablesToExtend as $tableName => $tableConfig) {
            if (!empty($tableConfig['evaluation']) && !empty($tableConfig['evaluation']['detailPid'])) {
                $tableTitle = $GLOBALS['TCA'][$tableName]['ctrl']['title'] ?: $tableName;

                if (\str_starts_with($tableTitle, 'LLL:')) {
                    $tableTitle = LocalizationUtility::translate($tableTitle, $extKey);
                }

                $tables[$tableName] = $tableTitle;
            }
        }

        $table = $this->modParams['table'];
        if ($table && $table !== 'pages') {
            // @extensionScannerIgnoreLine
            $records = DatabaseUtility::getRecords($table, $this->recordId, true);
            if ($records && $this->modParams['record'] && isset($records[$this->modParams['record']])) {
                $evaluationUid = $this->modParams['record'];
            }

            if ($evaluationUid) {
                $evaluation = $this->evaluationService->getEvaluation($evaluationUid, $table);
            }

            $this->view->assignMultiple(
                [
                    'record' => $this->modParams['record'],
                    'records' => $records,
                    'showRecords' => true,
                ]
            );
        } else {
            $table = 'pages';
            $languages = [];
            $pageUid = (int)($page['uid'] ?? 0);

            // get available languages
            $pageOverlays = DatabaseUtility::getPageLanguageOverlays($pageUid);
            // get languages
            $allLanguages = DatabaseUtility::getLanguagesInBackend($pageUid);

            $languages[0] = $allLanguages[0];

            if ($pageOverlays !== []) {
                $languagesUids = array_keys($pageOverlays);
                foreach ($allLanguages as $langUid => $languageLabel) {
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
            $evaluation = $this->evaluationService->getEvaluation($page);
            $evaluationUid = $page['_PAGES_OVERLAY_UID'] ?? $pageUid;
            $langResult = $page['_PAGES_OVERLAY_LANGUAGE'] ?? 0;
            $this->view->assignMultiple(
                [
                    'lang' => $languageParam,
                    'languages' => $languages,
                    'langDisplay' => $allLanguages[$langResult],
                ]
            );
        }

        if (isset($evaluation)) {
            $results = $evaluation->getResultsAsArray();

            if(is_array($results) && isset($results['Percentage'])) {
                $this->view->assign('score', $results['Percentage']);
                unset($results['Percentage']);
            }

            $this->view->assignMultiple(
                [
                    'evaluation' => $evaluation,
                    'results' => $results,
                ]
            );
        }

        $emConf = ConfigurationUtility::getEmConfiguration();

        $this->view->assignMultiple(
            [
                'evaluationUid' => $evaluationUid,
                'emConf' => $emConf,
                'page' => $page,
                'tables' => $tables,
                'table' => $table,
            ]
        );

        $this->jsModules = [
            '@clickstorm/cs-seo/Evaluation.js',
        ];

        $this->jsFiles = [
            'jquery.min.js',
        ];

        $this->cssFiles = [
            'Icons.css',
            'Evaluation.css',
        ];

        return $this->htmlResponse($this->wrapModuleTemplate());
    }

    /**
     * Renders the menu so that it can be returned as response to an AJAX call
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request)
    {
        // get parameter
        $postdata = file_get_contents('php://input');
        $attr = json_decode($postdata, true);

        // prepare data array
        $tableName = 'pages';
        $uid = $attr['entry']['uid'];
        $field = $attr['field'];

        // check for language overlay
        if (isset($attr['entry']['_PAGES_OVERLAY']) && isset($GLOBALS['TCA']['pages']['columns'][$field])) {
            $uid = $attr['entry']['_PAGES_OVERLAY_UID'];
        }

        // update map
        $data[$tableName][$uid][$field] = $attr['value'];

        // update data
        $dataHandler = $this->getDataHandler();
        $dataHandler->datamap = $data;
        $dataHandler->process_datamap();
        $response = new HtmlResponse('');
        if (!empty($dataHandler->errorLog)) {
            $response->getBody()->write('Error: ' . implode(',', $dataHandler->errorLog));
        }

        return $response;
    }
}
