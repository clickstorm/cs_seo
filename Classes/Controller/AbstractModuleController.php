<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Clickstorm\CsSeo\Utility\TSFEUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class ModuleController
 */
abstract class AbstractModuleController extends ActionController
{
    public static $session_prefix = 'tx_csseo_';
    public static $mod_name = 'web_CsSeoMod1';
    public static $uriPrefix = 'tx_csseo_web_csseomod1';

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
            $modParam = GeneralUtility::_GP($name)?: GlobalsUtility::getBackendUser()->getSessionData(static::$session_prefix . $name);
            if(is_numeric($modParam)) {
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

        // Shortcut in doc header
        $shortcutButton = $moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $shortcutButton->setModuleName(self::$mod_name)
            ->setDisplayName(GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:mlang_labels_tablabel'
            ))
            ->setSetVariables(['tree']);
        $moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($shortcutButton);

        // The page will show only if there is a valid page and if this page
        // may be viewed by the user
        $pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        if ($pageinfo) {
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($pageinfo);
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
}
