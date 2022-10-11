<?php

namespace Clickstorm\CsSeo\Service\Backend;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Backend\Form\Exception\AccessDeniedException;
use TYPO3\CMS\Backend\Form\Exception\DatabaseRecordException;
use TYPO3\CMS\Backend\Form\Exception\DatabaseRecordWorkspaceDeletePlaceholderException;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormService
{
    public array $elementsData;
    /**
     * @var int|mixed
     */
    public $errorC;
    public int $newC;
    /**
     * @var int|mixed
     */
    public $viewId;
    public string $viewId_addParams;
    /**
     * @var mixed|array<string, mixed>|array<string, mixed[]>
     */
    public $overrideVals;
    /**
     * @var mixed|mixed[]
     */
    public $defVals;
    /**
     * Render an editform for specific table, see
     * @return string HTML form elements wrapped in tables
     * @see \TYPO3\CMS\Backend\Controller\EditDocumentController
     *
     */
    public function makeEditForm(string $table, int $theUid, string $columns): string
    {
        // Initialize variables
        $this->elementsData = [];
        $this->errorC = 0;
        $this->newC = 0;
        $editForm = '';
        $beUser = GlobalsUtility::getBackendUser();
        $deleteAccess = false;

        $command = 'edit';

        try {
            $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
            $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
            $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);

            // Reset viewId - it should hold data of last entry only
            $this->viewId = 0;
            $this->viewId_addParams = '';

            $formDataCompilerInput = [
                'tableName' => $table,
                'vanillaUid' => (int)$theUid,
                'command' => 'edit',
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ];

            if (is_array($this->overrideVals) && is_array($this->overrideVals[$table])) {
                $formDataCompilerInput['overrideValues'] = $this->overrideVals[$table];
            }
            if (!empty($this->defVals) && is_array($this->defVals)) {
                $formDataCompilerInput['defaultValues'] = $this->defVals;
            }

            $formData = $formDataCompiler->compile($formDataCompilerInput);

            if ($table === 'pages') {
                $this->viewId = $formData['databaseRow']['uid'];
            } elseif (!empty($formData['parentPageRow']['uid'])) {
                $this->viewId = $formData['parentPageRow']['uid'];
                // Adding "&L=xx" if the record being edited has a languageField with a value larger than zero!
                if (!empty($formData['processedTca']['ctrl']['languageField'])
                    && is_array($formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']])
                    && $formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']][0] > 0
                ) {
                    $this->viewId_addParams = '&L=' . $formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']][0];
                }
            }

            $lockInfo = BackendUtility::isRecordLocked($table, $formData['databaseRow']['uid']);
            if ($lockInfo) {
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $lockInfo['msg'],
                    '',
                    FlashMessage::WARNING
                );
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $defaultFlashMessageQueue->enqueue($flashMessage);
            }

            $this->elementsData[] = [
                'table' => $table,
                'uid' => $formData['databaseRow']['uid'],
                'pid' => $formData['databaseRow']['pid'],
                'cmd' => $command,
                'deleteAccess' => $deleteAccess
            ];

            if ($command !== 'new') {
                BackendUtility::lockRecords(
                    $table,
                    $formData['databaseRow']['uid'],
                    $table === 'tt_content' ? $formData['databaseRow']['pid'] : 0
                );
            }

            $formData['fieldListToRender'] = $columns;

            $formData['renderType'] = 'outerWrapContainer';
            $formResult = $nodeFactory->create($formData)->render();

            $html = $formResult['html'];

            $formResult['html'] = '';
            $formResult['doSaveFieldName'] = 'doSave';

            $formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);
            $formResultCompiler->mergeResult($formResult);
            $res = $formResultCompiler->printNeededJSFunctions();

            $editForm = $formResultCompiler->addCssFiles();
            $editForm .= $html;
            $editForm .= $formResultCompiler->printNeededJSFunctions();
        } catch (AccessDeniedException $e) {
            $this->errorC++;
            // Try to fetch error message from "recordInternals" be user object
            // @todo: This construct should be logged and localized and de-uglified
            $message = (empty($beUser->errorMsg)) ? $message = $e->getMessage() . ' ' . $e->getCode() : $beUser->errorMsg;
            $title = GlobalsUtility::getLanguageService()
                ->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.noEditPermission');
            $editForm .= $this->getInfobox($message, $title);
        } catch (DatabaseRecordException | DatabaseRecordWorkspaceDeletePlaceholderException $e) {
            $editForm .= $this->getInfobox($e->getMessage());
        }

        return $editForm;
    }

    /**
     * Helper function for rendering an Infobox
     *
     * @param string $message
     * @param string|null $title
     * @return string
     */
    protected function getInfobox(string $message, ?string $title = null): string
    {
        return '<div class="callout callout-danger">' .
            '<div class="media">' .
            '<div class="media-left">' .
            '<span class="fa-stack fa-lg callout-icon">' .
            '<i class="fa fa-circle fa-stack-2x"></i>' .
            '<i class="fa fa-times fa-stack-1x"></i>' .
            '</span>' .
            '</div>' .
            '<div class="media-body">' .
            ($title ? '<h4 class="callout-title">' . htmlspecialchars($title) . '</h4>' : '') .
            '<div class="callout-body">' . htmlspecialchars($message) . '</div>' .
            '</div>' .
            '</div>' .
            '</div>';
    }
}
