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
 * Render the seo and social meta data for records in frontend
 *
 * @package Clickstorm\CsSeo\UserFunc
 */
class HeaderData {

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    function __construct() {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
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

                // render content
                $headerData = $this->renderContent($meta);

                return $headerData;
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

                if ($uid) {
                    if($checkOnly) {
                        return true;
                    }
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
            $rowOL = $GLOBALS['TSFE']->sys_page->getRecordOverlay($tableSettings['table'], $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);

            if (!empty($rowOL)) {
                $row = $rowOL;
            }
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
            $title = $tsfeUtility->getFinalTitle($meta['title'], $meta['title_only']);
        } else {
            // fallback to page title
            $pageTitleFunc = GeneralUtility::makeInstance(PageTitle::class);
            $title = $pageTitleFunc->render('', array());
        }

        $content .= '<title>' . $this->escapeContent($title) . '</title>';

        // description
        $content .= $this->printMetaTag('description', $this->escapeContent($meta['description']));

        // hreflang & canonical
        $typoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
        unset($typoLinkConf['parameter.']);
        $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

        // get active table and uid
        $tables = self::getPageTS();
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $currentItem = self::getCurrentTable($tables, $cObj);

        $allLanguagesFromItem = $this->getAllLanguagesFromItem($currentItem['table'], $currentItem['uid']);

        $currentLanguageUid = $GLOBALS['TSFE']->sys_language_uid;


        // canonical
	    $canonicalTypoLinkConf = [];
        if($meta['canonical']) {
	        $canonicalTypoLinkConf['parameter'] = $meta['canonical'];
	        $canonicalTypoLinkConf['forceAbsoluteUrl'] = 1;
        } else {
            $canonicalTypoLinkConf = $typoLinkConf;

            // if a fallback is shown, set canonical to the language of the ordered item
            if (!in_array($currentLanguageUid, $allLanguagesFromItem)) {
                unset($canonicalTypoLinkConf['additionalParams.']);
                $canonicalTypoLinkConf['additionalParams'] = '&L=' . $this->getLanguageFromItem($currentItem['table'], $currentItem['uid']);
            }
        }
	    $canonical = $this->cObj->typoLink_URL($canonicalTypoLinkConf);

        // index
        if($meta['no_index']) {
            $content .= $this->printMetaTag('robots', 'noindex,nofollow');
        } else {
            $content .= '<link rel="canonical" href="' . $canonical . '" />';
        }

        // hreflang
        // if the item for the current language uid exists and
        // the item is not set to no index and
        // the item points not to another page as canonical and
        // the TS setting hreflang.enabled is set to 1
        if (in_array($currentLanguageUid, $allLanguagesFromItem) && !$meta['no_index'] && !$meta['canonical'] && $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['enable']) {
            $langIds = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['ids']);
            $langKeys = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['keys']);

            $hreflangTypoLinkConf = $typoLinkConf;

            foreach ($langIds as $key => $langId) {
                // set hreflang only for languages of the TS setup and if the language is also localized for the item
                // if the language doesn't exist for the item and a fallback language is shown, the hreflang is not set and the canonical points to the fallback url
                if (in_array($langId, $allLanguagesFromItem)) {
                    unset($hreflangTypoLinkConf['additionalParams.']);
                    $hreflangTypoLinkConf['additionalParams'] = '&L=' . $langId;
                    $hreflangUrl = $this->cObj->typoLink_URL($hreflangTypoLinkConf);
                    $content .= '<link rel="alternate" hreflang="' . $langKeys[$key] . '" href="' . $hreflangUrl . '" />';
                }
            }
        }

        // og:title
        $ogTitle = $meta['og_title'] ?: $title;
        $content .= $this->printMetaTag('og:title', $this->escapeContent($ogTitle), 1);

        // og:description
        $ogDescription = $meta['og_description'] ?: $meta['description'];
        $content .= $this->printMetaTag('og:description', $this->escapeContent($ogDescription), 1);

        // og:image
	    $ogImageURL = $pluginSettings['social.']['defaultImage'];

        if ($meta['og_image']) {
	        $ogImageURL = $this->getImagePath('og_image', $meta['uid']);
        }

        if($ogImageURL) {
	        $finalOgImageURL = $this->getScaledImagePath($ogImageURL, $pluginSettings['social.']['openGraph.']['image.']);
	        $content .= $this->printMetaTag('og:image', $finalOgImageURL, 1);
        }

        // og:type
        $content .= $this->printMetaTag('og:type', 'website', 1);

        // og:url
        $content .= $this->printMetaTag('og:url', $canonical, 1);

        // og:locale
        $content .= $this->printMetaTag('og:locale', $GLOBALS['TSFE']->config['config']['locale_all'], 1);

        // og:site_name
        $content .= $this->printMetaTag('og:site_name', $this->escapeContent($GLOBALS['TSFE']->tmpl->sitetitle), 1);

        // twitter title
        if($meta['tw_title']) {
            $content .= $this->printMetaTag('twitter:title', $this->escapeContent($meta['tw_title']));
        }

        // twitter description
        if($meta['tw_description']) {
            $content .= $this->printMetaTag('twitter:description', $this->escapeContent($meta['tw_description']));
        }

        // twitter image and type
	    if ($meta['tw_image'] || $meta['og_image']) {
	    	if($meta['tw_image']) {
			    $twImageURL = $this->getImagePath('tw_image', $meta['uid']);
		    } else {
			    $twImageURL = $ogImageURL;
		    }
		    $content .= $this->printMetaTag('twitter:card', 'summary_large_image');
	    } else {
		    $twImageURL = $pluginSettings['social.']['twitter.']['defaultImage']?: $pluginSettings['social.']['defaultImage'];
		    $content .= $this->printMetaTag('twitter:card', 'summary');
	    }

	    if($twImageURL) {
		    $finalTwImageURL = $this->getScaledImagePath($twImageURL, $pluginSettings['social.']['twitter.']['image.']);
		    $content .= $this->printMetaTag('twitter:image', $finalTwImageURL);
	    }

        // creator
        $content .= $this->printMetaTag(
        	'twitter:creator',
	        $this->escapeContent($meta['tw_creator']?:$pluginSettings['social.']['twitter.']['creator'])
        );

        return $content;
    }

    /**
     * Returns an image path for the given field and uid
     *
     * @param string $field
     * @param string $uid
     * @return string the image path
     */
    protected function getImagePath($field, $uid) {
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
            return $fileObjects[0]->getOriginalFile()->getPublicUrl();
        }

    }

	/**
	 * Return an URL to the scaled image
	 *
	 * @param string $originalFile uid or path of the file
	 * @param array $imageSize width and height as keys
	 * @return string
	 */
    protected function getScaledImagePath($originalFile, $imageSize) {
	    $conf = array(
		    'file' => $originalFile,
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

	/**
	 * @param string $content
	 * @return string
	 */
	protected function escapeContent($content) {
		return htmlentities(preg_replace('/\s\s+/', ' ', preg_replace('#<[^>]+>#', ' ', $content)));
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
    protected function getAllLanguagesFromItem($table, $uid) {
        $languageIds = array();
        $whereClause = '(l10n_parent = ' . $uid . ' OR uid = ' . $uid . ')';
        $whereClause .= $GLOBALS['TSFE']->sys_page->enableFields($table);
        $allItems = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', $table, $whereClause);

        foreach ($allItems as $item) {
            $languageIds[] = $item['sys_language_uid'];
        }

        return $languageIds;
    }

    /**
     * @param string $table
     * @param string $uid
     * @return array
     */
    protected function getLanguageFromItem($table, $uid) {
        $whereClause = 'uid = ' . $uid;
        $whereClause .= $GLOBALS['TSFE']->sys_page->enableFields($table);
        $item = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('sys_language_uid', $table, $whereClause);

        return $item[0]['sys_language_uid'];
    }

}