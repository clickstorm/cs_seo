<?php

namespace Clickstorm\CsSeo\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ModuleController
 */
abstract class AbstractModuleController extends ActionController
{
    public $modTSconfig;

    public ?DataHandler $dataHandler = null;
    public static string $session_prefix = 'tx_csseo_';
    public static string $mod_name = 'web_CsSeoMod1';
    public static string $uriPrefix = 'tx_csseo_web_csseomod1';
    public static string $l10nFileName = 'web';
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
        protected readonly PageRenderer          $pageRenderer,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {
    }

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

        if (empty($this->recordId)) {
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
        $sessionParams = GlobalsUtility::getBackendUser()->getSessionData(static::$session_prefix) ?: $this->modParams;

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
        GlobalsUtility::getBackendUser()->setAndSaveSessionData(
            static::$session_prefix,
            $this->modParams
        );
    }

    /**
     * @return DataHandler
     */
    protected function getDataHandler(): DataHandler
    {
        if (!(property_exists($this, 'dataHandler') && $this->dataHandler !== null)) {
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
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $shortcutButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $type = $shortcutButton->getType();
        $shortcutButton->setRouteIdentifier(static::$mod_name)
            ->setDisplayName(GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/Module/' . static::$l10nFileName . '.xlf:mlang_labels_tablabel'
            ));
        $buttonBar->addButton($shortcutButton);

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
            $this->moduleTemplate->getDocHeaderComponent()->setMetaInformation($metaInfo);
        }

        if (count(static::$menuActions) > 1) {
            // Main drop down in doc header
            $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menu->setIdentifier('action');
            foreach (static::$menuActions as $menuKey) {
                $menuItem = $menu->makeMenuItem();
                /** @var UriBuilder $uriBuilder */
                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $menuItem->setHref((string)$uriBuilder->buildUriFromRoute(
                    static::$mod_name . '_' . $menuKey
                ))
                    ->setTitle(GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:layouts.module.action.' . $menuKey
                    ));

                if ($this->actionMethodName === $menuKey . 'Action') {
                    $menuItem->setActive(true);
                }
                $menu->addMenuItem($menuItem);
            }
            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
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

    protected function addModuleButtons(ButtonBar $buttonBar): void
    {
    }
}
