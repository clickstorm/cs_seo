<?php

namespace Clickstorm\CsSeo\Service;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Clickstorm\CsSeo\Event\ModifyEvaluationPidEvent;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * crawl the page
 *
 * Class FrontendPageService
 */
class FrontendPageService
{
    /**
     * @var int
     */
    protected $lang;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $pageInfo
     * @param string $tableName
     * @return array
     * @throws \TYPO3\CMS\Core\Exception
     * @throws \TYPO3\CMS\Core\Routing\UnableToLinkToPageException
     */
    public function getFrontendPage(array $pageInfo, string $tableName = 'pages')
    {
        $result = [];

        if ($tableName === 'pages') {
            $allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();
            $noIndex = $pageInfo['tx_csseo_no_index'] ?? 0;
            if ($noIndex || !in_array($pageInfo['doktype'], $allowedDoktypes)) {
                return [];
            }
        }

        $params = '';
        $paramId = $pageInfo['l10n_parent'] !== 0 ? $pageInfo['l10n_parent'] : $pageInfo['uid'];

        if ($tableName && $tableName !== 'pages') {
            // record
            $tableSettings = ConfigurationUtility::getTableSettings($tableName);
            if (isset($tableSettings['evaluation']) && !empty($tableSettings['evaluation'])) {
                $params = str_replace('|', $pageInfo['uid'], $tableSettings['evaluation']['getParams']);
                $paramId = $tableSettings['evaluation']['detailPid'];
                if (isset($pageInfo['sys_language_uid']) && (int)$pageInfo['sys_language_uid'] > 0) {
                    $params .= '&L=' . $pageInfo['sys_language_uid'];
                }
            }
        } elseif ($pageInfo['sys_language_uid'] > 0) {
            $params = '&L=' . $pageInfo['sys_language_uid'];
        } else {
            $params = '&L=0';
        }

        // modify page id PSR-14 event
        $paramId = $this->eventDispatcher->dispatch(new ModifyEvaluationPidEvent(
            $paramId,
            $params,
            $tableName,
            $pageInfo
        ))->getPid();

        // build url
        $result['url'] = BackendUtility::getPreviewUrl($paramId, '', null, '', '', $params);

        // fetch url
        $response = GeneralUtility::makeInstance(RequestFactory::class)->request(
            $result['url'],
            'GET',
            [
                'headers' => ['X-CS-SEO' => '1'],
                'http_errors' => false
            ]
        );

        if (in_array($response->getStatusCode(), [0, 200])) {
            $result['content'] = $response->getBody()->getContents();
        } else {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $response->getReasonPhrase(),
                '',
                FlashMessage::ERROR
            );

            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('tx_csseo');
            $flashMessageQueue->enqueue($flashMessage);
        }

        return $result;
    }
}
