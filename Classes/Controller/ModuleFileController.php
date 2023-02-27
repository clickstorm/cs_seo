<?php

namespace Clickstorm\CsSeo\Controller;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use Clickstorm\CsSeo\Service\Backend\FormService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\FileUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class ModuleFileController extends AbstractModuleController
{
    public static string $session_prefix = 'tx_csseo_file_';
    public static string $mod_name = 'file_CsSeoModFile';
    public static string $uriPrefix = 'tx_csseo_file_csseomodfile';
    public static string $l10nFileName = 'file';

    protected array $modParams = ['action' => '', 'id' => '', 'recursive' => 1];

    protected ?File $image = null;

    public static array $menuActions = [
        'showEmptyImageAlt',
    ];

    protected array $cssFiles = [
        'Icons.css',
        'Lib/select2.css',
        'ModuleFile.css',
    ];

    protected int $storageUid = 0;
    protected string $identifier = '';
    protected int $offset = 0;
    protected int $numberOfImagesWithoutAlt = 0;

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

        $this->requireJsModules = [
            'TYPO3/CMS/Backend/ContextMenu',
            'TYPO3/CMS/Backend/Notification',
            'TYPO3/CMS/Backend/InfoWindow',
        ];

        if ($this->storageUid) {
            $includeSubfolders = (bool)$this->modParams['recursive'];

            $result = DatabaseUtility::getImageWithEmptyAlt(
                $this->storageUid,
                $this->identifier,
                $includeSubfolders,
                true
            );
            $this->numberOfImagesWithoutAlt = array_values($result[0])[0];

            // force offset to be smaller than number of all images without alt text
            if ($this->offset > 0 && $this->offset >= $this->numberOfImagesWithoutAlt) {
                $this->offset = $this->numberOfImagesWithoutAlt - 1;
            }

            $result = DatabaseUtility::getImageWithEmptyAlt(
                $this->storageUid,
                $this->identifier,
                $includeSubfolders,
                true,
                true
            );
            $numberOfAllImages = array_values($result[0])[0];

            if ($numberOfAllImages) {
                $numberOfImagesWithAlt = $numberOfAllImages - $this->numberOfImagesWithoutAlt;
                $percentOfImages = $numberOfImagesWithAlt / $numberOfAllImages * 100;
                $this->view->assignMultiple([
                    'numberOfImagesWithoutAlt' => $this->numberOfImagesWithoutAlt,
                    'indexOfCurrentImage' => $this->offset + 1,
                    'numberOfImagesWithAlt' => $numberOfImagesWithAlt,
                    'percentOfImages' => $percentOfImages,
                ]);
            }

            $this->view->assignMultiple([
                'numberOfAllImages' => $numberOfAllImages,
                'identifier' => $this->identifier,
            ]);

            $imageRow = DatabaseUtility::getImageWithEmptyAlt(
                $this->storageUid,
                $this->identifier,
                $includeSubfolders,
                false,
                false,
                $this->offset
            );

            $configuredColumns = ['alternative'];
            $additionalColumns = ConfigurationUtility::getEmConfiguration()['modFileColumns'] ?: '';

            $configuredColumns = array_merge($configuredColumns, explode(',', $additionalColumns));

            $columns = [];
            foreach ($configuredColumns as $col) {
                if ($GLOBALS['TCA']['sys_file_metadata']['columns'][$col] && $GLOBALS['TCA']['sys_file_metadata']['columns'][$col]['label']) {
                    $columns[$col] = $GLOBALS['TCA']['sys_file_metadata']['columns'][$col]['label'];
                }
            }

            $this->view->assign('columns', $columns);

            if (isset($imageRow[0]) && isset($imageRow[0]['uid'])) {
                $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
                $files = $dataMapper->map(File::class, $imageRow);
                $this->image = $files[0];
                $formService = GeneralUtility::makeInstance(FormService::class);
                $metadataUid = (int)$this->image->getOriginalResource()->getProperties()['metadata_uid'];

                // if no metadata record is there, create one
                if ($metadataUid === 0) {
                    $this->image->getOriginalResource()->getMetaData()->save();
                    $metadataUid = (int)$this->image->getOriginalResource()->getProperties()['metadata_uid'];
                }

                $editForm =$formService->makeEditForm('sys_file_metadata', $metadataUid, implode(',', $configuredColumns));

                $this->view->assignMultiple([
                    'offset' => $this->offset,
                    'editForm' => $editForm,
                    'image' => $files[0],
                ]);
            }
        }

        return $this->htmlResponse($this->wrapModuleTemplate());
    }

    /**
     * @throws FileDoesNotExistException
     * @throws NoSuchArgumentException
     */
    public function updateAction(): ResponseInterface
    {
        $uid = $this->request->hasArgument('uid') ? (int)$this->request->getArgument('uid') : 0;
        $data = GeneralUtility::_GP('data')['sys_file_metadata'] ?? '';

        if ($uid && $data) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $file = $resourceFactory->getFileObject($uid);

            $file->getMetaData()->add(array_values($data)[0])->save();

            if ($file->getProperty('alternative')) {
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $file->getName() . ' ' . GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.success.message'
                    ) . ': \n\'' . $file->getProperty('alternative') . '\'',
                    GlobalsUtility::getLanguageService()->sL(
                        'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.update.success.header'
                    ),
                    AbstractMessage::OK, // [optional] the severity defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
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
                    AbstractMessage::ERROR
                );
            }

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier(static::$mod_name);
            $messageQueue->addMessage($message);
        }

        return new ForwardResponse('showEmptyImageAlt');
    }

    protected function addModuleButtons(ButtonBar $buttonBar): void
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        if ($this->image) {
            if ($this->image->getOriginalResource()->getProperties()['metadata_uid']) {
                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $params = [
                    'edit' => [
                        'sys_file_metadata' => [
                            $this->image->getOriginalResource()->getProperties()['metadata_uid'] => 'edit',
                        ],
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                ];

                $editButton = $buttonBar->makeLinkButton()
                    ->setHref((string)$uriBuilder->buildUriFromRoute('record_edit', $params))
                    ->setDataAttributes([
                        'togglelink' => '1',
                        'toggle' => 'tooltip',
                        'placement' => 'bottom',
                    ])
                    ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:edit'))
                    ->setIcon($iconFactory->getIcon('actions-document-edit', Icon::SIZE_SMALL));
                $buttonBar->addButton($editButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
            }

            $infoButton = $buttonBar->makeLinkButton()
                ->setHref('#')
                ->setDataAttributes([
                    'dispatch-action' => 'TYPO3.InfoWindow.showItem',
                    'dispatch-args-list' => '_FILE,' . $this->image->getOriginalResource()->getUid(),
                ])
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:showInfo'))
                ->setIcon($iconFactory->getIcon('actions-info', Icon::SIZE_SMALL));
            $buttonBar->addButton($infoButton, ButtonBar::BUTTON_POSITION_LEFT, 2);

            $viewButton = $buttonBar->makeLinkButton()
                ->setDataAttributes([
                    'dispatch-action' => 'TYPO3.WindowManager.localOpen',
                    'dispatch-args-list' => $this->image->getOriginalResource()->getPublicUrl(),
                ])
                ->setHref('#')
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.view'))
                ->setIcon($iconFactory->getIcon('actions-eye', Icon::SIZE_SMALL));
            $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 3);

            $prevButton = $buttonBar->makeLinkButton()
                ->setDisabled($this->offset <= 0)
                ->setHref((string)$this->uriBuilder->uriFor(null, ['offset' => $this->offset - 1]))
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.prev'))
                ->setIcon($iconFactory->getIcon('actions-chevron-left', Icon::SIZE_SMALL));

            $buttonBar->addButton($prevButton, ButtonBar::BUTTON_POSITION_LEFT, 4);

            $nextOffset = $this->offset + 1;
            $nextButton = $buttonBar->makeLinkButton()
                ->setDisabled($nextOffset >= $this->numberOfImagesWithoutAlt)
                ->setHref((string)$this->uriBuilder->uriFor(null, ['offset' => $nextOffset]))
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.next'))
                ->setIcon($iconFactory->getIcon('actions-chevron-right', Icon::SIZE_SMALL));

            $buttonBar->addButton($nextButton, ButtonBar::BUTTON_POSITION_LEFT, 4);

            $saveButton = $buttonBar->makeInputButton()
                ->setForm('EditDocumentController')
                ->setIcon($iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL))
                ->setName('_savedok')
                ->setShowLabelText(true)
                ->setTitle(GlobalsUtility::getLanguageService()->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.submit_and_next'
                ))
                ->setValue('1');

            $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 5);
        }

        $recursiveButton = $buttonBar->makeInputButton()
            ->setForm('ModForm')
            ->setIcon($iconFactory->getIcon('apps-pagetree-category-expand-all', Icon::SIZE_SMALL))
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
