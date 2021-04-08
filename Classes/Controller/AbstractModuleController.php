<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ModuleController
 */
abstract class AbstractModuleController extends ActionController
{
    public static $session_prefix = 'tx_csseo_';
    public static $mod_name = 'web_CsSeoMod1';
    public static $uriPrefix = 'tx_csseo_web_csseomod1';
    public static $flashMessageDurationInSeconds = 5;

    protected array $menuSetup = [];
    /**
     * @var array
     */
    protected $modParams = ['action' => '', 'id' => 0, 'lang' => 0, 'depth' => 1, 'table' => 'pages', 'record' => 0];

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected array $cssFiles = [];

    /**
     * @var array
     */
    protected $jsFiles = [];

    protected string $jsInlineCode = '';

    /**
     * @var array
     */
    protected $requireJsModules = [];

    /**
     * Initialize action
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function initializeAction()
    {
        // initialize page/be_user TSconfig settings
        $this->id = (int)GeneralUtility::_GP('id');
        $this->modTSconfig = BackendUtility::getPagesTSconfig($this->id)['mod.']['SHARED.'] ?? [];

        // initialize settings of the module
        $this->initializeModParams();
        if (!$this->request->hasArgument('action') && $this->modParams['action']) {
            $this->request->setArgument('action', $this->modParams['action']);
            // @extensionScannerIgnoreLine
            $this->forward($this->modParams['action']);
        }
    }

    /**
     * initialize the settings for the current view
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializeModParams()
    {
        foreach ($this->modParams as $name => $value) {
            $modParam = GeneralUtility::_GP($name) !== null ? GeneralUtility::_GP($name) : GlobalsUtility::getBackendUser()->getSessionData(static::$session_prefix . $name);
            if (is_numeric($modParam)) {
                $modParam = (int)$modParam;
            }
            $this->modParams[$name] = $modParam;

            if ($this->request->hasArgument($name)) {
                $arg = $this->request->getArgument($name);
                $this->modParams[$name] = ($name === 'action' || $name === 'table') ? $arg : (int)$arg;
            }
            GlobalsUtility::getBackendUser()->setAndSaveSessionData(static::$session_prefix . $name,
                $this->modParams[$name]);
        }
    }

    /**
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected function getDataHandler()
    {
        if (!isset($this->dataHandler)) {
            $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $this->dataHandler->start(null, null);
        }

        return $this->dataHandler;
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
            $moduleTemplate->getPageRenderer()->addCssFile('EXT:cs_seo/Resources/Public/Css/' . $cssFile);
        }

        $this->jsInlineCode .= $this->renderFlashMessages();

        if ($this->jsInlineCode) {
            $moduleTemplate->getPageRenderer()->addJsInlineCode('csseo-inline', $this->jsInlineCode);
        }

        // Shortcut in doc header
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $shortcutButton = $moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $shortcutButton->setModuleName(self::$mod_name)
            ->setDisplayName(GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:mlang_labels_tablabel'
            ))
            ->setSetVariables(['tree']);
        $buttonBar->addButton($shortcutButton);

        $this->addModuleButtons($buttonBar);

        // The page will show only if there is a valid page and if this page
        // may be viewed by the user
        if (is_numeric($this->modParams['id'])) {
            $metaInfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        } else {
            $metaInfo = [
                'combined_identifier' => $this->modParams['id'],
            ];
        }

        if ($metaInfo) {
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($metaInfo);
        }

        // Main drop down in doc header
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('action');
        foreach ($this->menuSetup as $menuKey) {
            $menuItem = $menu->makeMenuItem();
            /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $menuItem->setHref((string)$uriBuilder->buildUriFromRoute(
                static::$mod_name,
                [static::$uriPrefix => ['action' => $menuKey, 'Controller' => 'Module']]
            ))
                ->setTitle(GlobalsUtility::getLanguageService()->sL(
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

    protected function renderFlashMessages(): string
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier(static::$mod_name);

        if($messageQueue->isEmpty()) {
            return '';
        }

        $messages = [];

        $severityMapping = [
            AbstractMessage::OK => 'success',
            AbstractMessage::ERROR => 'error',
            AbstractMessage::INFO => 'info',
            AbstractMessage::NOTICE => 'notice',
            AbstractMessage::WARNING => 'waring'
        ];

        foreach ($messageQueue->getAllMessages() as $flashMessage) {
            $method = $severityMapping[$flashMessage->getSeverity()] ?: 'info';
            $messages[] =
                'top.TYPO3.Notification.' . $method . '("' . $flashMessage->getTitle() . '", "' . $flashMessage->getMessage() . '", ' . static::$flashMessageDurationInSeconds .');';
        }

        return '
                if (top && top.TYPO3.Notification) {
                    ' . implode(LF, $messages) . '
                }
            ';

    }

    protected function addModuleButtons(ButtonBar $buttonBar): void
    {

    }
}
