<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 11.04.16
 * Time: 09:18
 */

namespace Clickstorm\CsSeo\Utility;

use \Clickstorm\CsSeo\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * crawl the page
 *
 * Class FrontendPageUtility
 * @package Clickstorm\CsSeo\Utility
 */
class FrontendPageUtility {

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
		if($this->pageInfo['doktype'] != 1 || $this->pageInfo['tx_csseo_no_index']) {
			return '';
		}

		if($this->pageInfo['sys_language_uid']) {
			$params = 'id=' . $this->pageInfo['pid'] . '&L=' .$this->pageInfo['sys_language_uid'];
		} else {
			$params = 'id=' . $this->pageInfo['uid'];
		}

		$domain = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		$url = $domain . '/index.php?' . $params;

		$report = [];
		$content = GeneralUtility::getUrl($url, 0, false, $report);

		return ($report['error'] == 0) ? $content : '';
	}

}