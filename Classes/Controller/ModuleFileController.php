<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\FileUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;

class ModuleFileController extends AbstractModuleController
{
    public static $session_prefix = 'tx_csseo_file_';
    public static $mod_name = 'file_CsSeoModFile';
    public static $uriPrefix = 'tx_csseo_file_csseomodfile';

    /** @var File */
    protected $image;

    protected array $menuSetup = [
        'showEmptyImageAlt'
    ];

    protected array $cssFiles = [
        'Icons.css',
        'Lib/select2.css',
        'ModuleFile.css'
    ];

    protected int $storageUid;
    protected string $identifier;

    public function initializeAction()
    {
        parent::initializeAction();

        $this->storageUid = FileUtility::getStorageUidFromCombinedIdentifier($this->modParams['id']);
        $this->identifier = FileUtility::getIdentifierFromCombinedIdentifier($this->modParams['id']);
    }

    public function showEmptyImageAltAction()
    {
        $this->requireJsModules = [
            'TYPO3/CMS/Backend/ContextMenu'
        ];

        $result = DatabaseUtility::getImageWithEmptyAlt($this->storageUid, $this->identifier, true);
        $numberOfImagesWithoutAlt = array_values($result[0])[0];
        $result = DatabaseUtility::getImageWithEmptyAlt($this->storageUid, $this->identifier, true, true);
        $numberOfAllImages = array_values($result[0])[0];

        if ($numberOfAllImages) {
            $numberOfImagesWithAlt = $numberOfAllImages - $numberOfImagesWithoutAlt;
            $percentOfImages = $numberOfImagesWithAlt / $numberOfAllImages * 100;
            $this->view->assignMultiple([
                'numberOfImagesWithAlt' => $numberOfImagesWithAlt,
                'percentOfImages' => $percentOfImages
            ]);
        }

        $this->view->assignMultiple([
            'numberOfAllImages' => $numberOfAllImages,
            'identifier' => $this->identifier
        ]);

        $imageRow = DatabaseUtility::getImageWithEmptyAlt($this->storageUid, $this->identifier);
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

        if ($imageRow[0] && $imageRow[0]['uid']) {
            $dataMapper = $this->objectManager->get(DataMapper::class);
            $files = $dataMapper->map(File::class, $imageRow);
            $this->image = $files[0];
            $this->view->assign('image', $files[0]);
        }

        return $this->wrapModuleTemplate();
    }

    protected function addModuleButtons(ButtonBar $buttonBar): void
    {
        if ($this->image) {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            if($this->image->getOriginalResource()->getProperties()['metadata_uid']) {
                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $params = [
                    'edit' => [
                        'sys_file_metadata' => [
                            $this->image->getOriginalResource()->getProperties()['metadata_uid'] => 'edit'
                        ]
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
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
                    'dispatch-args-list' => 'sys_file,' . $this->image->getOriginalResource()->getUid(),
                ])
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:showInfo'))
                ->setIcon($iconFactory->getIcon('actions-info', Icon::SIZE_SMALL));
            $buttonBar->addButton($infoButton, ButtonBar::BUTTON_POSITION_LEFT, 2);

            $viewButton = $buttonBar->makeLinkButton()
                ->setHref('#')
                ->setOnClick('window.open(\'/' . $this->image->getOriginalResource()->getPublicUrl() . '\')')
                ->setTitle(GlobalsUtility::getLanguageService()->sL('LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.view'))
                ->setIcon($iconFactory->getIcon('actions-eye', Icon::SIZE_SMALL));
            $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 3);

            $saveButton = $buttonBar->makeInputButton()
                ->setForm('EditDocumentController')
                ->setIcon($iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL))
                ->setName('_savedok')
                ->setShowLabelText(true)
                ->setTitle(GlobalsUtility::getLanguageService()->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.btn.submit_and_next'
                ))
                ->setValue('1');

            $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 4);

            $recursiveButton = $buttonBar->makeInputButton()
                ->setForm('ModForm')
                ->setIcon($iconFactory->getIcon('apps-pagetree-category-expand-all', Icon::SIZE_SMALL))
                ->setName('recursive')
                ->setTitle(GlobalsUtility::getLanguageService()->sL(
                    'LLL:EXT:cs_seo/Resources/Private/Language/locallang.xlf:module.file.recursive'
                ))
                ->setValue('1');

            if($this->modParams['recursive']) {
                $recursiveButton->setClasses('active');
            }

            $buttonBar->addButton($recursiveButton, ButtonBar::BUTTON_POSITION_RIGHT, 4);

            // CSH
            $cshButton = $buttonBar->makeHelpButton()
                ->setModuleName('xMOD_csh_corebe')
                ->setFieldName('filetree');

            $buttonBar->addButton($cshButton);
        }
    }
}
