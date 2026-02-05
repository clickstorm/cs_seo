<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\ProviderRegistry;

/**
 * Class ModuleController
 */
abstract class AbstractModuleController extends ActionController
{
    public $modTSconfig;

    public ?DataHandler $dataHandler = null;
    public static string $mod_name = 'content_csseo';
    public static string $uriPrefix = 'tx_csseo_content';
    public static string $l10nFileName = 'content';
    public static int $flashMessageDurationInSeconds = 5;

    public static array $menuActions = [];

    protected array $modParams = ['action' => '', 'id' => 0, 'lang' => 0, 'depth' => 1, 'table' => 'pages', 'record' => 0];

    protected int $recordId = 0;

    protected array $cssFiles = [];

    protected array $jsFiles = [];

    protected string $jsInlineCode = '';

    protected array $jsModules = [];

    protected string $templateFile = '';

    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        protected PageRenderer $pageRenderer,
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected ComponentFactory $componentFactory,
    ) {}

    /**
     * @throws NoSuchArgumentException
     * @throws \JsonException
     */
    protected function initializeAction(): void
    {
        // initialize page/be_user TSconfig settings
        $this->recordId = (int)($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? 0);

        // initialize settings of the module
        $this->initializeModParams();

        if ($this->recordId === 0) {
            $this->recordId = (int)$this->modParams['id'];
        }

        if (!$this->request->hasArgument('action') && $this->modParams['action']) {
            $this->request = $this->request->withArgument('action', $this->modParams['action']);
            new ForwardResponse($this->modParams['action']);
        }

        if (is_int($this->recordId)) {
            $this->modTSconfig = BackendUtility::getPagesTSconfig($this->recordId)['mod.']['SHARED.'] ?? [];
        }

        // reset JavaScript and CSS files
        GeneralUtility::makeInstance(PageRenderer::class);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    /**
     * initialize the settings for the current view
     */
    protected function initializeModParams(): void
    {
        $sessionParams = GlobalsUtility::getBackendUser()->getModuleData(static::$mod_name) ?: $this->modParams;

        foreach (array_keys($this->modParams) as $name) {
            $modParam = $this->request->getParsedBody()[$name] ?? $this->request->getQueryParams()[$name] ?? $sessionParams[$name] ?? '';
            if (is_numeric($modParam)) {
                $modParam = (int)$modParam;
            }
            $this->modParams[$name] = $modParam;

            if ($this->request->hasArgument($name)) {
                $arg = $this->request->getArgument($name);
                // an id can be 0_99 in web module or 1:/user_upload in file module
                if ($name === 'id' && is_string($arg) && str_contains($arg, '_') && !str_contains($arg, ':')) {
                    // e.g. arg='0_99', the id should be the last number
                    $arg = (int)substr($arg, strrpos($arg, '_') + 1);
                }
                $this->modParams[$name] = is_numeric($arg) ? (int)$arg : $arg;
            }
        }
        GlobalsUtility::getBackendUser()->pushModuleData(
            static::$mod_name,
            $this->modParams
        );
    }

    /**
     * @return DataHandler
     */
    protected function getDataHandler(): DataHandler
    {
        if (!(property_exists($this, 'dataHandler') && $this->dataHandler instanceof DataHandler)) {
            $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $this->dataHandler->start([], []);
        }

        return $this->dataHandler;
    }

    protected function wrapModuleTemplate(): string
    {
        foreach ($this->jsFiles as $jsFile) {
            $this->pageRenderer->addJsFile('EXT:cs_seo/Resources/Public/JavaScript/' . $jsFile);
        }

        foreach ($this->jsModules as $jsModule) {
            $this->pageRenderer->loadJavaScriptModule($jsModule);
        }

        foreach ($this->cssFiles as $cssFile) {
            $this->pageRenderer->addCssFile('EXT:cs_seo/Resources/Public/Css/' . $cssFile);
        }

        $this->jsInlineCode .= $this->renderFlashMessages();

        if ($this->jsInlineCode !== '' && $this->jsInlineCode !== '0') {
            $this->pageRenderer->addJsInlineCode('csseo-inline', $this->jsInlineCode, true, false, true);
        }

        // Shortcut in doc header
        $l10nLabel = GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/Modules/' . static::$l10nFileName . '.xlf:title'
            );

        $this->moduleTemplate->getDocHeaderComponent()->setShortcutContext(
            routeIdentifier: static::$mod_name,
            displayName: $l10nLabel,
        );

        // add specific module buttons
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $this->addModuleButtons($buttonBar);

        // The page will show only if there is a valid page and if this page
        // may be viewed by the user
        if (is_numeric($this->modParams['id'])) {
            $permsClause = GlobalsUtility::getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW);
            // @extensionScannerIgnoreLine
            $metaInfo = BackendUtility::readPageAccess($this->recordId, $permsClause);
        } else {
            $metaInfo = [
                'combined_identifier' => $this->modParams['id'],
            ];
        }

        if ($metaInfo) {
            $this->moduleTemplate->getDocHeaderComponent()->setPageBreadcrumb($metaInfo);
        }

        if (count(static::$menuActions) > 1) {
            $this->moduleTemplate->makeDocHeaderModuleMenu();
        }

        return $this->moduleTemplate->render($this->templateFile);
    }

    protected function renderFlashMessages(): string
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier(static::$mod_name);

        if ($messageQueue->isEmpty()) {
            return '';
        }

        $messages = [];

        foreach ($messageQueue->getAllMessages() as $flashMessage) {
            $method = $flashMessage->getSeverity()->getCssClass();
            $messages[] =
                'top.TYPO3.Notification.' .
                $method .
                '("' . htmlspecialchars($flashMessage->getTitle()) .
                '", "' .
                htmlspecialchars($flashMessage->getMessage()) .
                '", ' .
                static::$flashMessageDurationInSeconds . ');';
        }

        return '
                if (top && top.TYPO3.Notification) {
                    ' . implode(chr(10), $messages) . '
                }
            ';
    }

    protected function addModuleButtons(ButtonBar $buttonBar): void {}
}
