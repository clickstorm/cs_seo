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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the open graph data
 *
 * @package Clickstorm\CsOpengraph
 */
class HeaderData {

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public $cObj;

	function __construct() {
		$this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class
		);
	}

	/**
	 * check if GP parameter is set
	 * @return boolean
	 */
	public static function checkSeoGP() {

		// get table settings
		$tables = self::getPageTS();

		if($tables) {
			$cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);

			// get active table name und uid
			$gpSEO = self::getCurrentTable($tables, $cObj, true);

			if($gpSEO) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * @return bool|string meta tags, if available
	 */
	public function getMetaTags($content, $conf) {
		// get table settings
		$tables = self::getPageTS();

		if ($tables) {
			// get active table name und settings
			$tableSettings = $this->getCurrentTable($tables, $this->cObj);

			if ($tableSettings) {
				// get record
				$record = $this->getRecord($tableSettings);

				if($record['_LOCALIZED_UID']) {
					$tableSettings['uid'] = $record['_LOCALIZED_UID'];
				}
				// db meta
				$meta = $this->getMetaProperties($tableSettings);

				// db fallback
				if(isset($tableSettings['fallback'])) {
					foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
						if(empty($meta[$seoField]) && !empty($record[$fallbackField])) {
							$meta[$seoField] = $record[$fallbackField];
						}
					}
				}

				if ($meta) {
					// render content
					$headerData = $this->renderContent($meta);

					return $headerData;
				}
			}
		}
		return false;
	}

	/**
	 * get the page TS Settings for tx_csseo
	 * @return array|bool
	 */
	public static function getPageTS() {
		$pageTSConfig = BackendUtility::getPagesTSconfig($GLOBALS['TSFE']->id);

		return isset($pageTSConfig['tx_csseo.']) ? $pageTSConfig['tx_csseo.'] : false;
	}

	/**
	 * Check if extension detail view or page properties should be used
	 *
	 * @param $tables
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
	 * @param bool $checkOnly
	 * @return array|bool
	 */
	public static function getCurrentTable($tables, $cObj, $checkOnly = false) {
		foreach ($tables as $key => $table) {
			if (isset($tables[$key . '.']['enable'])) {
				$settings = $tables[$key . '.'];
				$uid = intval($cObj->getData($settings['enable']));
                if ($settings['canonicalFallbackLanguage'] != '') {
                    $canonicalFallbackLanguage = intval($settings['canonicalFallbackLanguage']);
                } else {
                    $canonicalFallbackLanguage = NULL;
                }

				if ($uid) {
					if($checkOnly) {
						return true;
					}
					$data = array(
						'table' => $table,
						'uid' => $uid,
						'canonicalFallbackLanguage' => $canonicalFallbackLanguage,
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
	protected function getRecord($tableSettings) {
		$where = 'uid = ' . $tableSettings['uid'];
		$where  .= $GLOBALS['TSFE']->sys_page->enableFields($tableSettings['table']);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$tableSettings['table'],
			$where,
			'',
			'',
			1
		);
		$row = $res[0];

		if (is_array($row) && $row['sys_language_uid'] != $GLOBALS['TSFE']->sys_language_content && $GLOBALS['TSFE']->sys_language_contentOL) {
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($tableSettings['table'], $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
		}

		return $row;
	}


	/**
	 * render the meta tags
	 *
	 * @param $meta
	 * @return string
	 */
	protected function renderContent($meta) {
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

        // hreflang & canonical
        $typoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
        unset($typoLinkConf['parameter.']);
        $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

        // get active table and uid
        $tables = self::getPageTS();
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $currentItem = self::getCurrentTable($tables, $cObj);

        $allLanguagesFormItem = $this->getAllLanguagesFormItem($currentItem['table'], $currentItem['uid']);

        $currentLanguageUid = $GLOBALS['TSFE']->sys_language_uid;

        // hreflang

        // if the item for the current language uid exists and
        // the item is not set to no index and
        // the item points not to another page as canonical and
        // the TS setting hreflang.enabled is set to 1
        if (in_array($currentLanguageUid, $allLanguagesFormItem) && !$meta['no_index'] && !$meta['canonical'] && $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['enable']) {
            $langIds = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['ids']);
            $langKeys = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['keys']);

            $hreflangTypoLinkConf = $typoLinkConf;

            foreach ($langIds as $key => $langId) {
                if (in_array($langId, $allLanguagesFormItem)) {
                    $hreflangTypoLinkConf['additionalParams'] = '&L=' . $langId;
                    $hreflangUrl = $this->cObj->typoLink_URL($hreflangTypoLinkConf);

                    $content .= '<link rel="alternate" hreflang="' . $langKeys[$key] . '" href="' . $hreflangUrl . '" />';
                }
            }
        }

        // canonical
        if($meta['canonical']) {
            $canonical = $this->cObj->getTypoLink_URL($meta['canonical']);
        } else {
            // if a fallback is shown set canonical to the fallback language
            if (!in_array($currentLanguageUid, $allLanguagesFormItem) && !is_null($currentItem['canonicalFallbackLanguage'])) {
                $typoLinkConf['additionalParams'] = '&L=' . $currentItem['canonicalFallbackLanguage'];
            }
            $canonical = $this->cObj->typoLink_URL($typoLinkConf);
        }

		// index
		if($meta['no_index']) {
			$content .= $this->printMetaTag('robots', 'noindex,nofollow');
		} else {
			$content .= '<link rel="canonical" href="' . $canonical . '" />';
		}

		// og:title
		$ogTitle = $meta['og_title'] ?: $title;
		$content .= $this->printMetaTag('og:title', $ogTitle, 1);

		// og:description
		$ogDescription = $meta['og_description'] ?: $meta['description'];
		$content .= $this->printMetaTag('og:description', $ogDescription, 1);

		// og:image
		if ($meta['og_image']) {
			$imageURL = $this->getImageUrl('og_image', $meta['uid'], $pluginSettings['social.']['openGraph.']['image.']);
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
		$content .= $this->printMetaTag('og:site_name', $GLOBALS['TSFE']->tmpl->sitetitle, 1);

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
			$imageURL = $this->getImageUrl('tw_image', $meta['uid'], $pluginSettings['social.']['twitter.']['image.']);
			$content .= $this->printMetaTag('twitter:image', $imageURL);
		}

		// creator
		$content .= $this->printMetaTag('twitter:creator', $meta['tw_creator']?:$pluginSettings['social.']['twitter.']['creator']);

		return $content;
	}

	/**
	 * @param string $field
	 * @param string $uid
	 * @param array $imageSize
	 * @return string
	 */
	protected function getImageUrl($field, $uid, $imageSize) {
		/** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			\TYPO3\CMS\Core\Resource\FileRepository::class
		);
		$fileObjects = $fileRepository->findByRelation(
			'tx_csseo_domain_model_meta',
			'tx_csseo_' . $field,
			$uid
		);

		if($fileObjects[0]) {
			$conf = array(
				'file' => $fileObjects[0]->getOriginalFile()->getUid(),
				'file.' => array(
					'height' => $imageSize['height'],
					'width' => $imageSize['width']
				)
			);
			$imgUri = $this->cObj->cObjGetSingle('IMG_RESOURCE', $conf);
			$conf = array(
				'parameter' => $imgUri,
				'forceAbsoluteUrl' => 1
			);
			return $this->cObj->typoLink_URL($conf);
		}

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

    /**
     * @param string $table
     * @param string $uid
     * @return array
     */
    protected function getAllLanguagesFormItem($table, $uid) {
        $languageIds = array();
        $timestamp = time();
        $whereClause = '(l10n_parent = ' . $uid . ' OR uid = ' . $uid . ') AND deleted = 0 AND hidden = 0 AND t3ver_state <= 0 AND starttime <= ' . $timestamp . ' AND (endtime=0 OR endtime > ' . $timestamp . ')';
        $allItems = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', $table, $whereClause);

        foreach ($allItems as $item) {
            $languageIds[] = $item['sys_language_uid'];
        }

        return $languageIds;
    }
}