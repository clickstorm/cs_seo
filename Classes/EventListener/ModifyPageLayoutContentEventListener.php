<?php

namespace Clickstorm\CsSeo\EventListener;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Clickstorm\CsSeo\Utility\LanguageUtility;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Listen To the ModifyPageLayoutContentEvent and add the evaluation results to the page
 */
class ModifyPageLayoutContentEventListener
{
    public const EVALUATION_IN_PAGE_MODULE_HEADER = 0;
    public const EVALUATION_IN_PAGE_MODULE_FOOTER = 1;
    public const EVALUATION_IN_PAGE_MODULE_DISABLED = 2;

    protected string $resourcesPath = 'EXT:cs_seo/Resources/';

    protected int $currentPageUid = 0;

    protected array $pageInfo = [];

    protected array $csSeoConf = [];

    protected ModuleData $moduleData;

    protected int $currentSysLanguageUid = 0;

    public function __construct(
        private readonly ViewFactoryInterface $viewFactory,
        private readonly PageRenderer         $pageRenderer,
    )
    {
    }


    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $this->currentPageUid = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        $this->moduleData = $event->getRequest()->getAttribute('moduleData');
        $this->csSeoConf = ConfigurationUtility::getEmConfiguration();

        if ($this->showEvaluationInPageModule()) {
            $this->initPageInfo();
            if ($this->pageCanBeIndexed()) {
                // template
                $this->loadCss();
                $this->loadJavascript();

                $view = $this->initializeView();

                // @extensionScannerIgnoreLine
                $results = $this->getResultsOfPage($this->currentPageUid);
                $score = $results['Percentage'] ?? 0;
                unset($results['Percentage']);

                $view->assignMultiple(
                    [
                        'score' => $score,
                        'results' => $results,
                        'page' => BackendUtility::readPageAccess(
                            $this->currentPageUid,
                            GlobalsUtility::getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)
                        ),
                        'userHasAccessToPageEvaluationModule' => GlobalsUtility::getBackendUser()->check('modules', 'web_CsSeoMod1_pageEvaluation'),
                    ]
                );

                $content = $view->render('PageHook.html');

                if ((int)$this->csSeoConf['inPageModule'] === static::EVALUATION_IN_PAGE_MODULE_FOOTER) {
                    $event->addFooterContent($content);
                } else {
                    $event->addHeaderContent($content);
                }
            }
        }
    }

    protected function initializeView(): ViewInterface
    {
        // load partial paths info from TypoScript
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $tsSetup =
            $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );

        $layoutPaths = [$this->resourcesPath . 'Private/Layouts/'];
        $templatePaths = [$this->resourcesPath . 'Private/Templates/'];
        $partialPaths = [$this->resourcesPath . 'Private/Partials/'];

        // load partial paths info from typoscript
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: $tsSetup['module.']['tx_csseo.']['view.']['templateRootPaths.'] ?? $templatePaths,
            partialRootPaths: $tsSetup['module.']['tx_csseo.']['view.']['partialRootPaths.'] ?? $partialPaths,
            layoutRootPaths: $tsSetup['module.']['tx_csseo.']['view.']['layoutRootPaths.'] ?? $layoutPaths,
            request: GlobalsUtility::getTYPO3Request(),
        );

        return $this->viewFactory->create($viewFactoryData);
    }

    // show, if not disabled via Page TsConfig and if current mode is columns not languages
    protected function showEvaluationInPageModule(): bool
    {
        $tsConfig = BackendUtility::getPagesTSconfig($this->currentPageUid);
        $allowedViaExtConf = (int)$this->csSeoConf['inPageModule'] < static::EVALUATION_IN_PAGE_MODULE_DISABLED;
        $allowedViaPageTsConfig = isset($tsConfig['mod.']['web_layout.']['tx_csseo.']['disable']) ?
            !(bool)$tsConfig['mod.']['web_layout.']['tx_csseo.']['disable'] : true;
        $allowedViaModuleMode = (int)$this->moduleData->get('function') === 1;

        return $allowedViaExtConf && $allowedViaPageTsConfig && $allowedViaModuleMode;
    }

    protected function initPageInfo(): void
    {
        $this->pageInfo = BackendUtility::readPageAccess($this->currentPageUid, GlobalsUtility::getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW));
        $this->currentSysLanguageUid = (int)$this->moduleData->get('language');

        if ($this->currentSysLanguageUid) {
            $localizedPageInfo = BackendUtility::getRecordLocalization(
                'pages',
                $this->currentPageUid,
                $this->currentSysLanguageUid
            );
            if (isset($localizedPageInfo[0])) {
                $this->currentPageUid = $localizedPageInfo[0]['uid'];
                $this->pageInfo = $localizedPageInfo[0];
            }
        }
    }

    public function pageCanBeIndexed(): bool
    {
        $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();
        $languageIsAvailable = true;

        if ($this->currentSysLanguageUid > 0) {
            $languageIsAvailable = LanguageUtility::isLanguageEnabled($this->currentSysLanguageUid, $this->currentPageUid);
        }

        return $languageIsAvailable &&
            isset($this->pageInfo['doktype']) &&
            in_array($this->pageInfo['doktype'], $allowedDoktypes) &&
            $this->pageInfo['hidden'] == 0;
    }

    /**
     * Load the necessary css
     *
     * This will only be done when the referenced record is available
     */
    protected function loadCss(): void
    {
        // @todo Set to TRUE when finished
        $compress = false;
        $cssFiles = [
            'Icons.css',
            'Evaluation.css',
        ];

        $baseUrl = $this->resourcesPath . 'Public/Css/';

        // Load the wizards css
        foreach ($cssFiles as $cssFile) {
            // @extensionScannerIgnoreLine
            $this->pageRenderer->addCssFile($baseUrl . $cssFile, 'stylesheet', 'all', '', $compress, false);
        }
    }

    protected function loadJavascript(): void
    {
        // @extensionScannerIgnoreLine
        $this->pageRenderer->loadJavaScriptModule('@clickstorm/cs-seo/Evaluation.js');
    }

    /**
     * @throws \JsonException
     * @throws Exception
     */
    protected function getResultsOfPage(int $pageUid): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_csseo_domain_model_evaluation');
        $results = [];
        $tableName = 'pages';

        $res = $queryBuilder->select('results')
            ->from('tx_csseo_domain_model_evaluation')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName, Connection::PARAM_STR)
                )
            )->executeQuery();

        while ($row = $res->fetchAssociative()) {
            $results = unserialize($row['results']) ?: [];
        }

        return $results;
    }
}
