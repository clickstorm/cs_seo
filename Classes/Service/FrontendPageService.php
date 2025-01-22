<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use Clickstorm\CsSeo\Event\ModifyEvaluationPidEvent;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * crawl the page
 *
 * Class FrontendPageService
 */
class FrontendPageService
{
    protected int $lang = 0;

    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws Exception
     * @throws UnableToLinkToPageException
     */
    public function getFrontendPage(array $pageInfo, string $tableName = 'pages'): array
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
        $paramId = $pageInfo['l10n_parent'] ?? $pageInfo['uid'];

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
        $result['url'] = (string) PreviewUriBuilder::create($paramId)->withAdditionalQueryParameters($params)->buildUri();

        // fetch url
        $response = GeneralUtility::makeInstance(RequestFactory::class)->request(
            $result['url'],
            'GET',
            [
                'headers' => ['X-CS-SEO' => '1'],
                'http_errors' => false,
            ]
        );

        if (in_array($response->getStatusCode(), [0, 200])) {
            $result['content'] = $response->getBody()->getContents();
        } else {
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $response->getReasonPhrase(),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            /** @var FlashMessageService $flashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('tx_csseo');
            $flashMessageQueue->enqueue($flashMessage);
        }

        return $result;
    }
}
