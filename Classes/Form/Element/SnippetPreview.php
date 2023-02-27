<?php

namespace Clickstorm\CsSeo\Form\Element;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Google Search Results Preview
 *
 * Class PageTitle
 */
class SnippetPreview extends AbstractNode
{
    protected ?PageRenderer $pageRenderer = null;

    public function render(): array
    {
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

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
    protected function loadJavascript(): void
    {
        $this->pageRenderer->loadJavaScriptModule('@clickstorm/cs-seo/SnippetPreview.js');
    }

    protected function loadCss(): array
    {
        $stylesheetFiles = [];
        $cssFiles = [
            'Wizard.css',
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
     */
    protected function getBodyContent($data, $table): string
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

            // use page uid, l10n_parent or t3ver_oid if set
            if ($table === 'pages') {
                $pageUid = (int)$data['uid'];
                if (!empty($data['l10n_parent'])) {
                    if (is_array($data['l10n_parent'])) {
                        if (!empty($data['l10n_parent'][0])) {
                            $pageUid = (int)$data['l10n_parent'][0];
                        }
                    } elseif ((int)$data['l10n_parent'] > 0) {
                        $pageUid = (int)$data['l10n_parent'];
                    }
                }
                if (!empty($data['t3ver_oid'])) {
                    $pageUid = (int)$data['t3ver_oid'];
                }
            }

            // add page id to current request, so the backend configuration manager gets the right page
            $queryParams = $this->getCurrentRequest()->getQueryParams();
            $queryParams['id'] = $pageUid;
            $this->setCurrentRequest($this->getCurrentRequest()->withQueryParams($queryParams));

            // check if TS page type exists
            /** @var BackendConfigurationManager $backendConfigurationManager */
            $backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
            $fullTS = $backendConfigurationManager->getTypoScriptSetup();

            if (isset($fullTS['pageCsSeo']) || $GLOBALS['BE_USER']->workspace > 0) {
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
                if (isset($GLOBALS['TSFE'])) {
                    $siteTitle = $TSFEUtility->getSiteTitle();
                    $pageTitleSeparator = $TSFEUtility->getPageTitleSeparator();
                    $config = $TSFEUtility->getConfig();

                    if ($table == 'pages') {
                        $this->getTypoScriptFrontendController()->config['config']['noPageTitle'] = 0;

                        $this->getTypoScriptFrontendController()->generatePageTitle();

                        $pageTitle = static::getPageRenderer()->getTitle();

                        // get page path
                        $path = $TSFEUtility->getPagePath();

                        $fallback['title'] = 'title';
                        $fallback['uid'] = $data['uid'];
                        $fallback['table'] = $table;
                    } else {
                        $tableSettings = ConfigurationUtility::getTableSettings($data['tablenames']);

                        if ($tableSettings && is_array($tableSettings['fallback']) && !empty($tableSettings['fallback'])) {
                            $fallback = $tableSettings['fallback'];

                            /** @var QueryBuilder $queryBuilder */
                            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['tablenames']);

                            $queryBuilder
                                ->getRestrictions()
                                ->removeAll();

                            $res = $queryBuilder->select('*')
                                ->from($data['tablenames'])->where($queryBuilder->expr()->eq(
                                'uid',
                                $queryBuilder->createNamedParameter($data['uid_foreign'], \PDO::PARAM_INT)
                            ))->executeQuery()->fetchAll();

                            $row = $res[0];

                            foreach ($fallback as $seoField => $fallbackField) {
                                if (empty($data[$seoField])) {
                                    $data[$seoField] = $row[$fallbackField];
                                }
                            }

                            $fallback['uid'] = $data['uid_foreign'];
                            $fallback['table'] = $data['tablenames'];
                        }

                        $pageTitle = $TSFEUtility->getFinalTitle($data['title']);
                        $path = '';
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
                            'siteTitle' => $siteTitle,
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

    protected function getPageRenderer(): PageRenderer
    {
        if ($this->pageRenderer === null) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function getCurrentRequest(): ServerRequestInterface|null
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function setCurrentRequest(ServerRequestInterface $request): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }
}
