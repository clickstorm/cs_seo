<?php

namespace Clickstorm\CsSeo\Form\Element;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Localization\LanguageService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Service\FrontendConfigurationService;
use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Google Search Results Preview
 *
 * Class PageTitle
 */
class SnippetPreview extends AbstractFormElement
{
    protected const L10N_LABELS = [
        'statusOk',
        'statusOverflow',
        'charsMissing',
        'charsOver',
    ];

    protected ?PageRenderer $pageRenderer = null;

    public function __construct(
        private readonly ViewFactoryInterface $viewFactory,
        private readonly Context              $request,
    ) {
    }

    public function render(): array
    {
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        // first get input element
        $inputField = GeneralUtility::makeInstance(InputTextElement::class);
        $inputField->setData($this->data);
        $resultArray = $inputField->render();

        // Load necessary JavaScript
        $this->loadJavascript();

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
     */
    protected function loadJavascript(): void
    {
        $this->pageRenderer->loadJavaScriptModule('@clickstorm/cs-seo/FormEngine/Element/SnippetPreview.js');
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
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: [10 => 'EXT:cs_seo/Resources/Private/Templates/'],
            partialRootPaths: [10 => 'EXT:cs_seo/Resources/Private/Partials/'],
            layoutRootPaths: [10 => 'EXT:cs_seo/Resources/Private/Layouts/'],
        );
        $wizardView = $this->viewFactory->create($viewFactoryData);

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
            $queryParams = GlobalsUtility::getTYPO3Request()->getQueryParams();
            $queryParams['id'] = $pageUid;
            $this->setCurrentRequest(GlobalsUtility::getTYPO3Request()->withQueryParams($queryParams));

            // check if TS page type exists
            /** @var BackendConfigurationManager $backendConfigurationManager */
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $fullTS = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

            if (isset($fullTS['pageCsSeo']) || GlobalsUtility::getBackendUser()->workspace > 0) {
                // render page title
                $sysLanguageUid = is_array($data['sys_language_uid']) ? (int)current($data['sys_language_uid']) : (int)$data['sys_language_uid'];

                /** @var FrontendConfigurationService $FrontendConfigurationService */
                $FrontendConfigurationService = GeneralUtility::makeInstance(
                    FrontendConfigurationService::class,
                    $pageUid,
                    $sysLanguageUid
                );
                $fallback = [];
                $siteTitle = $FrontendConfigurationService->getSiteTitle();
                $pageTitleSeparator = $FrontendConfigurationService->getPageTitleSeparator();
                $config = $FrontendConfigurationService->getConfig();

                if ($table === 'pages') {

                    $pageTitle = $FrontendConfigurationService->getFinalTitle($data['seo_title'] ?: $data['title'] ?: '', !empty($data['tx_csseo_title_only']));

                    // get page path
                    $path = $FrontendConfigurationService->getPagePath();

                    $fallback['title'] = 'title';
                    $fallback['uid'] = $data['uid'];
                    $fallback['table'] = $table;
                } else {
                    $tableSettings = ConfigurationUtility::getTableSettings($data['tablenames']);

                    if ($tableSettings && !empty($tableSettings['fallback']) && is_array($tableSettings['fallback'])) {
                        $fallback = $tableSettings['fallback'];

                        /** @var QueryBuilder $queryBuilder */
                        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['tablenames']);

                        $queryBuilder
                            ->getRestrictions()
                            ->removeAll();

                        $res = $queryBuilder->select('*')
                            ->from($data['tablenames'])->where($queryBuilder->expr()->eq(
                                'uid',
                                $queryBuilder->createNamedParameter($data['uid_foreign'], Connection::PARAM_INT)
                            ))->executeQuery()->fetchAllAssociative();

                        $row = $res[0];

                        foreach ($fallback as $seoField => $fallbackField) {
                            if (!empty($data[$seoField])) {
                                continue; // Skip if already set
                            }

                            $value = '';

                            if (preg_match_all('/{([^}]+)}/', $fallbackField, $matches)) {
                                $value = $fallbackField;
                                foreach ($matches[1] as $fieldName) {
                                    $value = str_replace('{' . $fieldName . '}', $row[$fieldName] ?? '', $value);
                                }
                            } elseif (str_contains($fallbackField, '//')) {
                                $fields = array_map('trim', explode('//', $fallbackField));
                                foreach ($fields as $fieldName) {
                                    if (!empty($row[$fieldName])) {
                                        $value = $row[$fieldName];
                                        break;
                                    }
                                }
                            } else {
                                $value = $row[$fallbackField] ?? '';
                            }

                            // ✅ Remove any HTML tags and trim whitespace
                            $data[$seoField] = trim(strip_tags($value));
                        }

                        $fallback['uid'] = $data['uid_foreign'];
                        $fallback['table'] = $data['tablenames'];
                    }

                    $pageTitle = $FrontendConfigurationService->getFinalTitle($data['title'], !empty($data['title_only']));
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
                $wizardView->assign('error', 'no_ts');
            }
        } else {
            $wizardView->assign('error', 'no_data');
        }

        return $wizardView->render('Wizard.html');
    }

    protected function getTranslatedLabels(): array
    {
        $labels = [];
        $languageCode = $this->getLanguageService()->getLocale()->getLanguageCode();
        $languageFile = 'locallang.xlf';
        if ($languageCode) {
            $languageFile = $languageCode . '.locallang.xlf';
        }
        $labelPrefix = 'LLL:EXT:cs_seo/Resources/Private/Language/' . $languageFile . ':wizard.';

        foreach (self::L10N_LABELS as $labelKey) {
            $labels[$labelKey] = $this->getLanguageService()->sL($labelPrefix . $labelKey);
        }

        return $labels;
    }

    protected function getPageRenderer(): PageRenderer
    {
        if ($this->pageRenderer === null) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }

    protected function setCurrentRequest(ServerRequestInterface $request): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }
}
