<?php

namespace Clickstorm\CsSeo\Controller;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\FileUtility;
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

        $this->view->assignMultiple([
            'numberOfImagesWithoutAlt' => $numberOfImagesWithoutAlt,
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

            $this->view->assign('image', $files[0]);
        }

        return $this->wrapModuleTemplate();
    }


}
