<?php

namespace Clickstorm\CsSeo\Hook;

use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * hook to display the evaluation results in the page module
 *
 * Class pageHook
 */
class PageHook
{
    /**
     * @var mixed|object
     */
    public $pageRenderer;
    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var string $resourcesPath
     */
    protected $resourcesPath;

    /**
     * @var int
     */
    protected $currentPageUid = 0;

    /**
     * @var array
     */
    protected $pageInfo = [];

    /**
     * @var int
     */
    protected $currentSysLanguageUid = 0;

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
     * @throws InvalidExtensionNameException
     */
    public function render(array $params, PageLayoutController $parentObject): string
    {
        $tsConfig = BackendUtility::getPagesTSconfig($parentObject->id);
        $disableViaTsConfig = isset($tsConfig['mod.']['web_layout.']['tx_csseo.']['disable']) ?
            (bool)$tsConfig['mod.']['web_layout.']['tx_csseo.']['disable'] : false;

        // @extensionScannerIgnoreLine
        if ((int)$parentObject->MOD_SETTINGS['function'] === 1 && !$disableViaTsConfig) {
            $this->initPage($parentObject);
            if ($this->pageCanBeIndexed()) {
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
                        ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
                    );

                $layoutPaths = $tsSetup['module.']['tx_csseo.']['view.']['layoutRootPaths.'] ?? $layoutPaths;
                $partialPaths = $tsSetup['module.']['tx_csseo.']['view.']['partialRootPaths.'] ?? $partialPaths;

                $this->view->setLayoutRootPaths($layoutPaths);
                $this->view->setPartialRootPaths($partialPaths);

                $this->view->setTemplatePathAndFilename(
                    $this->resourcesPath . 'Private/Templates/PageHook.html'
                );

                // @extensionScannerIgnoreLine
                $results = $this->getResultsOfPage($this->currentPageUid);
                $score = $results['Percentage'] ?? 0;
                unset($results['Percentage']);

                $this->view->assignMultiple(
                    [
                        'score' => $score,
                        'results' => $results,
                        'page' => BackendUtility::readPageAccess(
                            $this->currentPageUid,
                            $GLOBALS['BE_USER']->getPagePermsClause(Permission::PAGE_SHOW)
                        ),
                    ]
                );

                return $this->view->render();
            }
        }

        return '';
    }

    protected function initPage(PageLayoutController $pageLayoutController): void
    {
        $this->currentSysLanguageUid = $pageLayoutController->MOD_SETTINGS['language'];
        $this->pageInfo = $pageLayoutController->pageinfo;
        $this->currentPageUid = $pageLayoutController->id;

        if ($this->currentSysLanguageUid) {
            $localizedPageInfo = BackendUtility::getRecordLocalization(
                'pages',
                $this->currentPageUid,
                $this->currentSysLanguageUid
            );
            if ($localizedPageInfo[0]) {
                $this->currentPageUid = $localizedPageInfo[0]['uid'];
                $this->pageInfo = $localizedPageInfo[0];
            }
        }
    }

    /**
     * @return bool
     */
    public function pageCanBeIndexed()
    {
        $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();

        return in_array($this->pageInfo['doktype'], $allowedDoktypes) && $this->pageInfo['hidden'] == 0;
    }

    /**
     * Load the necessary css
     *
     * This will only be done when the referenced record is available
     */
    protected function loadCss()
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
            $this->getPageRenderer()->addCssFile($baseUrl . $cssFile, 'stylesheet', 'all', '', $compress, false);
        }
    }

    /**
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        if (!(property_exists($this, 'pageRenderer') && $this->pageRenderer !== null)) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }

    /**
     * Load the necessary javascript
     *
     * This will only be done when the referenced record is available
     */
    protected function loadJavascript()
    {
        $this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/CsSeo/Evaluation');
    }

    /**
     * @param $pageInfo
     * @param $lang
     *
     * @return array
     */
    protected function getResultsOfPage($pageUid)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_csseo_domain_model_evaluation');
        $results = [];
        $tableName = 'pages';

        $res = $queryBuilder->select('results')
            ->from('tx_csseo_domain_model_evaluation')->where($queryBuilder->expr()->eq(
            'uid_foreign',
            $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT)
        ), $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableName)))->executeQuery();

        while ($row = $res->fetch()) {
            $results = unserialize($row['results']);
        }

        return $results;
    }
}
