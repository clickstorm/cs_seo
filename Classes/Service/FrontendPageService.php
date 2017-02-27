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

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * crawl the page
 *
 * Class FrontendPageService
 * @package Clickstorm\CsSeo\Service
 */
class FrontendPageService {

	/**
	 * @var array
	 */
	protected $pageInfo;

	/**
	 * @var int
	 */
	protected $lang;

	/**
	 * TSFEUtility constructor.
	 * @param array $pageInfo
	 */
	public function __construct($pageInfo) {
		$this->pageInfo = $pageInfo;
	}

	/**
	 * @return string
	 */
	public function getHTML() {
		$allowedDoktypes = ConfigurationUtility::getEvaluationDoktypes();
		if(!in_array($this->pageInfo['doktype'], $allowedDoktypes) || $this->pageInfo['tx_csseo_no_index']) {
			return '';
		}

        // add id and language
		if($this->pageInfo['sys_language_uid'] > 0) {
			$pid = $this->pageInfo['pid'];
			$params = 'id=' . $pid . '&L=' . $this->pageInfo['sys_language_uid'];
		} else {
			$pid = $this->pageInfo['uid'];
			$params = 'id=' . $pid;
		}

        // disable cache
        $params .= '&no_cache=1';

		$domain = BackendUtility::getViewDomain($pid);
		$url = $domain . '/index.php?' . $params;

		$report = [];
		$content = GeneralUtility::getUrl($url, 0, false, $report);

        if($report['message'] && $report['message'] != 'OK') {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $report['message'],
                "",
                FlashMessage::ERROR
            );
            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('tx_csseo');
            $flashMessageQueue->enqueue($flashMessage);
        }

		return in_array($report['error'], [0, 200]) ? $content : '';
	}
}