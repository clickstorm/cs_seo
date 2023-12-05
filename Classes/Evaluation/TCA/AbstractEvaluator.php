<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractEvaluator
{
    protected function addFlashMessage($message): void
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $GLOBALS['LANG']->sL(
                $message
            ),
            '',
            ContextualFeedbackSeverity::WARNING
        );
        /** @var FlashMessageService $flashMessageService  */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
