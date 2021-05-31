<?php

namespace Clickstorm\CsSeo\Evaluation\TCA;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractEvaluator
{
    protected function addFlashMessage($message)
    {
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $GLOBALS['LANG']->sL(
                $message
            ),
            '',
            FlashMessage::WARNING
        );
        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
