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
	 * @var int
	 */
	protected $pageUid;

	/**
	 * @var int
	 */
	protected $lang;

	/**
	 * TSFEUtility constructor.
	 * @param int $pageUid
	 * @param int $lang
	 */
	public function __construct($pageUid, $lang = 0) {
		$this->pageUid = $pageUid;
		$this->lang = $lang;
	}

	/**
	 * @return string
	 */
	public function getHTML() {
		$domain = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		$url = $domain . '/index.php?id=' . $this->pageUid . '&lang=' . $this->lang;
		return GeneralUtility::getUrl($url);
	}

}