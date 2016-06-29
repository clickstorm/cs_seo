<?php
namespace Clickstorm\CsSeo\UserFunc;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the open graph data
 *
 * @package Clickstorm\CsOpengraph
 */
class HeaderData {
	/**
	 * check if GP paramater is set
	 * @return boolean
	 */
	public static function checkSeoGP() {
		// get table settings
		$tables = self::getPageTS();

		if($tables) {
			/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj */
			$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
			);
			// get active table name und uid
			$gpSEO = self::getCurrentTableAndUid($tables, $cObj);
			if($gpSEO) {
				return true;
			}
		}


		return false;
	}
	
	/**
	 * @return bool|string og meta tags, if available
	 */
	public function getMetaTags($content, $conf) {
		// get table settings
		$tables = $this->getPageTS();

		if ($tables) {
			/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj */
			$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
			);

			// get active table name und settings
			$tableSettings = self::getCurrentTableAndUid($tables, $cObj);
			if ($tableSettings) {

				// db meta
				$meta = self::getMetaProperties($tableSettings);

				// db fallback
				if(isset($tableSettings['fallback'])) {
					$fallback = self::getFallbackProperties($tableSettings);
					foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
						if(empty($meta[$seoField]) && !empty($fallback[$fallbackField])) {
							$meta[$seoField] = $fallback[$fallbackField];
						}
					}
				}

				if ($meta) {
					// render content
					$headerData = self::renderContent($meta, $cObj);

					return $headerData;
				}
			}
		}
		return false;
	}

	/**
	 * get the page TS Settings for tx_csopengraph
	 * @return array|bool
	 */
	protected function getPageTS() {
		$pageTSConfig = BackendUtility::getPagesTSconfig($GLOBALS['TSFE']->id);

		return isset($pageTSConfig['tx_csseo.']) ? $pageTSConfig['tx_csseo.'] : false;
	}

	/**
	 * Check if extension detail view or page properties should be used
	 *
	 * @param $tables
	 * @return array|bool
	 */
	protected function getCurrentTableAndUid($tables, $cObj) {
		// check for extension detail view

		foreach ($tables as $key => $table) {
			if (isset($tables[$key . '.']['data'])) {
				$settings = $tables[$key . '.'];
				$uid = intval($cObj->getData($settings['data']));
				if ($uid) {
					$data = array(
						'table' => $table,
						'uid' => $uid,
					);
					if(isset($settings['fallback.']) && count($settings['fallback.']) > 0) {
						$data['fallback'] = $settings['fallback.'];
					}
					return $data;
				}
			}
		}

		// page
		$pagesTable = $GLOBALS['TSFE']->sys_language_uid > 0 ? 'pages_language_overlay' : 'pages';
		if (in_array($pagesTable, $tables)) {
			$pageUid = $GLOBALS['TSFE']->page['_PAGES_OVERLAY_UID'] ? : $GLOBALS['TSFE']->id;

			return array($pagesTable, $pageUid);
		}

		return false;
	}

	/**
	 * DB query to get the current meta properties
	 *
	 * @param $tableSettings
	 * @return bool
	 */
	protected function getMetaProperties($tableSettings) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_csseo_domain_model_meta',
			'tablenames = "' . $tableSettings['table'] . '" AND uid_foreign = ' . $tableSettings['uid'] . ' AND deleted=0',
			'',
			'',
			1
		);

		return isset($res[0]) ? $res[0] : [];
	}

	/**
	 * DB query to get the fallback properties
	 *
	 * @param $tableSettings
	 * @return bool
	 */
	protected function getFallbackProperties($tableSettings) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$tableSettings['table'],
			'uid = ' . $tableSettings['uid'],
			'',
			'',
			1
		);

		return isset($res[0]) ? $res[0] : [];
	}


	/**
	 * render the meta tags
	 *
	 * @param $meta
	 * @return string
	 */
	protected function renderContent($meta, $cObj) {
		/** @var \Clickstorm\CsSeo\Utility\TSFEUtility $tsfeUtility */
		$tsfeUtility = GeneralUtility::makeInstance(\Clickstorm\CsSeo\Utility\TSFEUtility::class, $GLOBALS['TSFE']->id);
		$pluginSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.'];

		$content = '';

		$title = $meta['title'];

		// title
		if ($title) {
			$title = $meta['title'];

			if(!$meta['title_only']) {

				$title = $tsfeUtility->getFinalTitle($meta['title']);
			}
			$content .= '<title>' . $title . '</title>';
		}

		// description
		$content .= $this->printMetaTag('description', $meta['description']);

		// index
		$canonical = $meta['canonical'] ? $cObj->typoLink_URL($meta['canonical']) : GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
		if($meta['no_index']) {
			$content .= $this->printMetaTag('robots', 'noindex,nofollow');
		} else {
			$content .= $this->printMetaTag('canonical', $canonical);
		}

		// og:title
		$ogTitle = $meta['og_title'] ?: $title;
		$content .= $this->printMetaTag('og:title', $ogTitle, 1);

		// og:description
		$ogDescription = $meta['og_description'] ?: $meta['description'];
		$content .= $this->printMetaTag('og:description', $ogDescription, 1);

		// og:image
		if ($meta['og_image']) {
			$imageURL = $this->getImageUrl('og_image', $meta, $cObj);
			$content .= $this->printMetaTag('og:image', $imageURL, 1);
		} else {
			$content .= $this->printMetaTag('og:image', $pluginSettings['social.']['defaultImage'], 1);
		}

		// og:type
		$content .= $this->printMetaTag('og:type', 'website', 1);

		// og:url
		$content .= $this->printMetaTag('og:url', $canonical, 1);
		
		// og:locale
		$content .= $this->printMetaTag('og:locale', $GLOBALS['TSFE']->config['config']['locale_all'], 1);

		// og:site_name
		$content .= $this->printMetaTag('og:site_name', $tmpl->sitetitle, 1);


		// twitter
		$content .= $this->printMetaTag('twitter:card', 'summary');

		// twitter title
		if($meta['tw_title']) {
			$content .= $this->printMetaTag('twitter:title', $meta['tw_title']);
		}

		// twitter description
		if($meta['tw_description']) {
			$content .= $this->printMetaTag('twitter:description', $meta['tw_description']);
		}

		// twitter image
		if($meta['tw_image']) {
			$imageURL = $this->getImageUrl('tw_image', $meta, $cObj);
			$content .= $this->printMetaTag('twitter:image', $imageURL);
		}

		// creator
		$content .= $this->printMetaTag('twitter:creator', $meta['tw_creator']?:$pluginSettings['social.']['twitter.']['creator']);

		return $content;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool $property
	 * @return string
	 */
	protected function getImageUrl($field, $meta, $cObj) {
		/** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			\TYPO3\CMS\Core\Resource\FileRepository::class
		);
		$fileObjects = $fileRepository->findByRelation(
			'tx_csseo_domain_model_meta',
			$field,
			$meta['uid']
		);
		$conf = array(
			'file' => $fileObjects[0]->getOriginalFile()->getUid(),
			'file.' => array(
				'height' => '630c',
				'width' => '1200'
			)
		);
		$imgUri = $cObj->IMG_RESOURCE($conf);
		$conf = array(
			'parameter' => $imgUri,
			'forceAbsoluteUrl' => 1
		);
		return $cObj->typoLink_URL($conf);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool $property
	 * @return string
	 */
	protected function printMetaTag($name, $value, $property = false) {
		if(empty($value)) {
			return '';
		}

		$propertyString = $property ? 'property' : 'name';
		return '<meta ' . $propertyString . '="' . $name . '" content="' . $value . '" />';
	}
}