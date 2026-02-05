<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Service\Backend\GridService;
use Clickstorm\CsSeo\Service\EvaluationService;
use Clickstorm\CsSeo\Service\FrontendConfigurationService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\LanguageUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class ModuleController
 */
class ModuleContentController extends AbstractModuleController
{
    protected ?PageRepository $pageRepository = null;

    protected ?EvaluationService $evaluationService = null;

    protected ?GridService $gridService = null;

    public static array $menuActions = [
        'meta' => 'meta',
        'index' => 'index',
        'open_graph' => 'openGraph',
        'twitter_cards' => 'twitterCards',
        'structured_data' => 'structuredData',
        'results' => 'results',
        'evaluation' => 'evaluation',
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
    public function metaAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/PageMeta';
        $fieldNames = ['title', 'seo_title', 'tx_csseo_title_only', 'description'];

        // get title and settings from TypoScript
        $frontendConfigurationService = GeneralUtility::makeInstance(FrontendConfigurationService::class, $this->recordId, $this->modParams['lang']);
        $this->moduleTemplate->assign('previewSettings', json_encode($frontendConfigurationService->getPreviewSettings()));

        return $this->htmlResponse($this->generateGridView($fieldNames));
    }

    /**
     * Show Index properties
     */
    public function indexAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/Index';
        return $this->htmlResponse($this->generateGridView(['title', 'canonical_link', 'no_index', 'no_follow', 'no_search']));
    }

    /**
     * Show Open Graph properties
     */
    public function openGraphAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/OpenGraph';
        return $this->htmlResponse($this->generateGridView(['title', 'og_title', 'og_description', 'og_image']));
    }

    /**
     * Show Structure Data properties
     */
    public function structuredDataAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/StructuredData';
        return $this->htmlResponse($this->generateGridView(['title', 'tx_csseo_json_ld']));
    }

    /**
     * Show Twitter Cards properties
     */
    public function twitterCardsAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/TwitterCards';
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
    public function resultsAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/Results';
        return $this->htmlResponse($this->generateGridView(['title', 'tx_csseo_keyword', 'results'], true));
    }

    /**
     * Show page evaluation results
     */
    public function evaluationAction(): ResponseInterface
    {
        $this->templateFile = 'ModuleContent/Evaluation';
        $page = $this->pageRepository->getPage((int)$this->modParams['id'], true);
        $evaluationUid = 0;
        $extKey = 'cs_seo';
        $tables = [
            'pages' => LocalizationUtility::translate($GLOBALS['TCA']['pages']['ctrl']['title'], 'CsSeo'),
        ];

        $tablesToExtend = ConfigurationUtility::getTablesToExtend();

        foreach ($tablesToExtend as $tableName => $tableConfig) {
            if (!empty($tableConfig['evaluation']) && !empty($tableConfig['evaluation']['detailPid'])) {
                $tableTitle = $GLOBALS['TCA'][$tableName]['ctrl']['title'] ?: $tableName;

                if (\str_starts_with((string)$tableTitle, 'LLL:')) {
                    $tableTitle = LocalizationUtility::translate($tableTitle, 'CsSeo');
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
                $evaluation = $this->evaluationService->getEvaluation(['uid' => $evaluationUid], $table);
            }

            $this->moduleTemplate->assignMultiple(
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
            $allLanguages = LanguageUtility::getLanguagesInBackend($pageUid);

            $languages[0] = $allLanguages[0];

            if ($pageOverlays !== []) {
                $languagesUids = array_keys($pageOverlays);
                foreach ($allLanguages as $langUid => $languageLabel) {
                    if ($langUid > 0 && in_array($langUid, $languagesUids) && LanguageUtility::isLanguageEnabled($langUid, $pageUid)) {
                        $languages[$langUid] = $languageLabel;
                    }
                }
            }

            // get page
            $languageParam = (int)$this->modParams['lang'];
            if ($languageParam > 0) {
                $page = $this->pageRepository->getPageOverlay($page, $languageParam);
            }
            $evaluation = $this->evaluationService->getEvaluation($page);
            $evaluationUid = $page['_LOCALIZED_UID'] ?? $pageUid;
            $langResult = $page['sys_language_uid'] ?? 0;
            $this->moduleTemplate->assignMultiple(
                [
                    'lang' => $languageParam,
                    'languages' => $languages,
                    'langDisplay' => $allLanguages[$langResult],
                ]
            );
        }

        if (isset($evaluation)) {
            $results = $evaluation->getResultsAsArray();

            if (is_array($results) && isset($results['Percentage'])) {
                $this->moduleTemplate->assign('score', $results['Percentage']);
                unset($results['Percentage']);
            }

            $this->moduleTemplate->assignMultiple(
                [
                    'evaluation' => $evaluation,
                    'results' => $results,
                ]
            );
        }

        $emConf = ConfigurationUtility::getEmConfiguration();

        $this->moduleTemplate->assignMultiple(
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
    public function update(ServerRequestInterface $request): HtmlResponse|ResponseInterface
    {
        // get parameter
        $postdata = file_get_contents('php://input');
        $attr = json_decode($postdata, true);

        // prepare data array
        $tableName = 'pages';
        $uid = $attr['entry']['uid'];
        $field = $attr['field'];

        // check for language overlay
        if (isset($attr['entry']['_LOCALIZED_UID']) && isset($GLOBALS['TCA']['pages']['columns'][$field])) {
            $uid = $attr['entry']['_LOCALIZED_UID'];
        }

        // update map
        $data[$tableName][$uid][$field] = $attr['value'];

        // update data
        $dataHandler = $this->getDataHandler();
        $dataHandler->datamap = $data;
        $dataHandler->process_datamap();
        $response = new HtmlResponse('');
        if ($dataHandler->errorLog !== []) {
            $response->getBody()->write('Error: ' . implode(',', $dataHandler->errorLog));
        }

        return $response;
    }

    protected function generateGridView(array $fieldNames, bool $showResults = false): string
    {
        $gridService = GeneralUtility::makeInstance(GridService::class);

        $gridService->setModParams($this->modParams);
        $gridService->setFieldNames($fieldNames);
        $gridService->setShowResults($showResults);

        $this->cssFiles = $gridService->getCssFiles();
        $this->jsFiles = $gridService->getJsFiles();

        $this->moduleTemplate->assignMultiple($gridService->processFields());

        return $this->wrapModuleTemplate();
    }
}
