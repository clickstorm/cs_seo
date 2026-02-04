<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Domain\Model\Dto\FileModuleOptions;
use Clickstorm\CsSeo\Service\Backend\FormService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\FileUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\AllowedMethodsTrait;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\ProviderRegistry;

class ModuleFileController extends AbstractModuleController
{
    use AllowedMethodsTrait;

    public static string $session_prefix = 'tx_csseo_file_';
    public static string $mod_name = 'file_CsSeoModFile';
    public static string $uriPrefix = 'tx_csseo_file_csseomodfile';
    public static string $l10nFileName = 'file';

    protected array $modParams = ['action' => '', 'id' => '', 'recursive' => 1, 'onlyReferenced' => 0];

    protected ?File $image = null;

    public static array $menuActions = [
        'showEmptyImageAlt',
    ];

    protected array $cssFiles = [
        'Icons.css',
        'ModuleFile.css',
    ];

    protected string $templateFile = 'ModuleFile/ShowEmptyImageAlt';

    protected int $storageUid = 0;
    protected string $identifier = '';
    protected int $offset = 0;
    protected int $numberOfImagesWithoutAlt = 0;

    public function __construct(
        protected PageRenderer $pageRenderer,
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected ComponentFactory $componentFactory,
        private readonly ResourceFactory $resourceFactory,
        private readonly FlashMessageService $flashMessageService,
        private readonly IconFactory $iconFactory,
        protected readonly UriBuilder $backendUriBuilder,
    ) {
        parent::__construct($pageRenderer, $moduleTemplateFactory, $componentFactory);
    }

    public function initializeAction(): void
    {
        parent::initializeAction();

        $this->storageUid = FileUtility::getStorageUidFromCombinedIdentifier($this->modParams['id']);
        $this->identifier = FileUtility::getIdentifierFromCombinedIdentifier($this->modParams['id']);
    }

    public function showEmptyImageAltAction(): ResponseInterface
    {
        BackendUtility::lockRecords();

        if ($this->request->hasArgument('offset')) {
            $this->offset = (int)$this->request->getArgument('offset');
        }

        $this->jsModules = [
            '@typo3/backend/context-menu.js',
            '@typo3/backend/notification.js',
            '@typo3/backend/info-window.js',
        ];

        if ($this->storageUid !== 0) {
            $fileModuleOptions = new FileModuleOptions(
                $this->storageUid,
                $this->identifier,
                (bool)$this->modParams['recursive'],
                (bool)$this->modParams['onlyReferenced']
            );

            $result = DatabaseUtility::getImageWithEmptyAlt(
                $fileModuleOptions,
                true
            );

            $this->numberOfImagesWithoutAlt = $result[0] ?? 0;

            // Clamp offset to valid range (0 .. numberOfImagesWithoutAlt - 1)
            $maxOffset = max(0, $this->numberOfImagesWithoutAlt - 1);
            $this->offset = max(0, min($this->offset, $maxOffset));

            $result = DatabaseUtility::getImageWithEmptyAlt(
                $fileModuleOptions,
                true,
                true
            );

            $numberOfAllImages = $result[0] ?? 0;

            if ($numberOfAllImages) {
                $numberOfImagesWithAlt = $numberOfAllImages - $this->numberOfImagesWithoutAlt;
                $percentOfImages = $numberOfImagesWithAlt / $numberOfAllImages * 100;
                $this->moduleTemplate->assignMultiple([
                    'numberOfImagesWithoutAlt' => $this->numberOfImagesWithoutAlt,
                    'indexOfCurrentImage' => $this->offset + 1,
                    'numberOfImagesWithAlt' => $numberOfImagesWithAlt,
                    'percentOfImages' => $percentOfImages,
                ]);
            }

            $this->moduleTemplate->assignMultiple([
                'numberOfAllImages' => $numberOfAllImages,
                'identifier' => $this->identifier,
                'modParams' => $this->modParams,
            ]);

            $imageRow = DatabaseUtility::getImageWithEmptyAlt(
                $fileModuleOptions,
                false,
                false,
                $this->offset
            );

            $configuredColumns = ['alternative'];
            $additionalColumns = ConfigurationUtility::getEmConfiguration()['modFileColumns'] ?: '';

            $configuredColumns = array_merge($configuredColumns, explode(',', $additionalColumns));

            $columns = [];
            foreach ($configuredColumns as $col) {
                if (isset($GLOBALS['TCA']['sys_file_metadata']['columns'][$col]['label'])) {
                    $columns[$col] = $GLOBALS['TCA']['sys_file_metadata']['columns'][$col]['label'];
                }
            }

            $this->moduleTemplate->assign('columns', $columns);

            if (isset($imageRow[0]) && isset($imageRow[0]['uid'])) {
                $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
                $files = $dataMapper->map(File::class, $imageRow);
                $this->image = $files[0];

                if ($this->image->getOriginalResource()->checkActionPermission('read')
                    && $this->image->getOriginalResource()->checkActionPermission('editMeta')) {
                    $formService = GeneralUtility::makeInstance(FormService::class);
                    $metadataUid = (int)$this->image->getOriginalResource()->getProperties()['metadata_uid'];

                    // if no metadata record is there, create one
                    if ($metadataUid === 0) {
                        $this->image->getOriginalResource()->getMetaData()->save();
                        $metadataUid = (int)$this->image->getOriginalResource()->getProperties()['metadata_uid'];
                    }

                    if ($this->modParams['onlyReferenced']) {
                        $this->moduleTemplate->assign('numberOfReferences', DatabaseUtility::getFileReferenceCount($this->image->getUid()));
                    }

                    $editForm = $formService->makeEditForm('sys_file_metadata', $metadataUid, implode(',', $configuredColumns));

                    $this->moduleTemplate->assignMultiple([
                        'offset' => $this->offset,
                        'editForm' => $editForm,
                        'image' => $files[0],
                    ]);
                } else {
                    $this->moduleTemplate->assign('error', 'no_access');
                }
            }

            // update the breadcrumb
            $breadCrumbItem = $this->image instanceof File ? $this->image->getOriginalResource() : null;

            if (!$breadCrumbItem instanceof \TYPO3\CMS\Core\Resource\File) {
                /** @var ResourceFactory $resourceFactory */
                $resourceFactory = $this->resourceFactory;
                $breadCrumbItem = $resourceFactory->getFolderObjectFromCombinedIdentifier(
                    $fileModuleOptions->getStorageUid() . ':' . $fileModuleOptions->getIdentifier()
                );
            }

            $this->moduleTemplate->getDocHeaderComponent()->setResourceBreadcrumb($breadCrumbItem);
        }

        return $this->htmlResponse($this->wrapModuleTemplate());
    }

    /**
     * @throws FileDoesNotExistException
     * @throws NoSuchArgumentException
     */
    public function updateAction(): ResponseInterface
    {
        $uid = (int)($this->request->getParsedBody()['uid'] ?? 0);
        $data = $this->request->getParsedBody()['data']['sys_file_metadata'] ?? false;

        $this->assertAllowedHttpMethod($this->request, 'POST');

        if ($uid > 0 && $data) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = $this->resourceFactory;
            $file = $resourceFactory->getFileObject($uid);

            if ($file->checkActionPermission('editMeta')) {
                $file->getMetaData()->add(array_values($data)[0])->save();
            }

            if ($file->getProperty('alternative')) {
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $file->getName() . ' ' . GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.success.message'
                    ) . ': \n\'' . $file->getProperty('alternative') . '\'',
                    GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.success.header'
                    ),
                    ContextualFeedbackSeverity::OK, // [optional] the severity defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                    false // [optional] whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is false)
                );
            } else {
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $file->getName() . ' ' . GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.error.message'
                    ),
                    GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.error.header'
                    ),
                    ContextualFeedbackSeverity::ERROR
                );
            }

            $flashMessageService = $this->flashMessageService;
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier(static::$mod_name);
            // @extensionScannerIgnoreLine
            $messageQueue->addMessage($message);
        }

        return new ForwardResponse('showEmptyImageAlt');
    }

    protected function addModuleButtons(ButtonBar $buttonBar): void
    {
        $iconFactory = $this->iconFactory;
        if ($this->image instanceof File) {
            $actionName = preg_replace('/Action$/', '', $this->request->getControllerActionName());

            if ($this->image->getOriginalResource()->getProperties()['metadata_uid']) {
                $params = [
                    'edit' => [
                        'sys_file_metadata' => [
                            $this->image->getOriginalResource()->getProperties()['metadata_uid'] => 'edit',
                        ],
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                ];

                $editButton = $this->componentFactory->createLinkButton()
                    ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('record_edit', $params))
                    ->setDataAttributes([
                        'togglelink' => '1',
                        'toggle' => 'tooltip',
                        'placement' => 'bottom',
                    ])
                    ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:edit'))
                    ->setIcon($iconFactory->getIcon('actions-document-edit', IconSize::SMALL));
                $buttonBar->addButton($editButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
            }

            $infoButton = $this->componentFactory->createLinkButton()
                ->setHref('#')
                ->setDataAttributes([
                    'dispatch-action' => 'TYPO3.InfoWindow.showItem',
                    'dispatch-args-list' => '_FILE,' . $this->image->getOriginalResource()->getUid(),
                ])
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:showInfo'))
                ->setIcon($iconFactory->getIcon('actions-info', IconSize::SMALL));
            $buttonBar->addButton($infoButton, ButtonBar::BUTTON_POSITION_LEFT, 2);

            $viewButton = $this->componentFactory->createLinkButton()
                ->setDataAttributes([
                    'dispatch-action' => 'TYPO3.WindowManager.localOpen',
                    'dispatch-args-list' => $this->image->getOriginalResource()->getPublicUrl(),
                ])
                ->setHref('#')
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.view'))
                ->setIcon($iconFactory->getIcon('actions-eye', IconSize::SMALL));
            $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 3);

            $prevButton = $this->componentFactory->createLinkButton()
                ->setDisabled($this->offset <= 0)
                ->setHref((string)$this->uriBuilder->uriFor($actionName, ['offset' => $this->offset - 1]))
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.prev'))
                ->setIcon($iconFactory->getIcon('actions-chevron-left', IconSize::SMALL));

            $buttonBar->addButton($prevButton, ButtonBar::BUTTON_POSITION_LEFT, 4);

            $nextOffset = $this->offset + 1;
            $nextButton = $this->componentFactory->createLinkButton()
                ->setDisabled($nextOffset >= $this->numberOfImagesWithoutAlt)
                ->setHref((string)$this->uriBuilder->uriFor($actionName, ['offset' => $nextOffset]))
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.next'))
                ->setIcon($iconFactory->getIcon('actions-chevron-right', IconSize::SMALL));

            $buttonBar->addButton($nextButton, ButtonBar::BUTTON_POSITION_LEFT, 4);

            $saveButton = $this->componentFactory->createInputButton()
                ->setForm('EditDocumentController')
                ->setIcon($iconFactory->getIcon('actions-document-save', IconSize::SMALL))
                ->setName('_save')
                ->setShowLabelText(true)
                ->setTitle(GlobalsUtility::getLanguageService()->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.submit_and_next'
                ))
                ->setValue('1');

            $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 5);
        }

        $onlyReferencedButton = $this->componentFactory->createInputButton()
            ->setForm('ModForm')
            ->setIcon($iconFactory->getIcon('actions-thumbtack', IconSize::SMALL))
            ->setName('onlyReferenced')
            ->setTitle(GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.onlyReferenced'
            ))
            ->setValue('1');

        if ($this->modParams['onlyReferenced']) {
            $onlyReferencedButton
                ->setClasses('active')
                ->setValue('0');
        }

        $buttonBar->addButton($onlyReferencedButton, ButtonBar::BUTTON_POSITION_RIGHT, 4);

        $recursiveButton = $this->componentFactory->createInputButton()
            ->setForm('ModForm')
            ->setIcon($iconFactory->getIcon('apps-pagetree-category-expand-all', IconSize::SMALL))
            ->setName('recursive')
            ->setTitle(GlobalsUtility::getLanguageService()->sL(
                'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.recursive'
            ))
            ->setValue('1');

        if ($this->modParams['recursive']) {
            $recursiveButton
                ->setClasses('active')
                ->setValue('0');
        }

        $buttonBar->addButton($recursiveButton, ButtonBar::BUTTON_POSITION_RIGHT, 4);
    }
}
