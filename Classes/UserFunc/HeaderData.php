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

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the seo and social meta data for records in frontend
 *
 * @package Clickstorm\CsSeo\UserFunc
 */
class HeaderData
{
    const TABLE_NAME_META = 'tx_csseo_domain_model_meta';

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    public function __construct()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * check if GP parameter is set
     *
     * @return boolean
     */
    public static function checkSeoGP()
    {
        // get table settings
        $tables = ConfigurationUtility::getPageTSconfig();
        if ($tables) {
            $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);

            // get active table name und uid
            $gpSEO = self::getCurrentTable($tables, $cObj, true);

            if ($gpSEO) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if extension detail view or page properties should be used
     *
     * @param $tables
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
     * @param bool $checkOnly
     *
     * @return array|bool
     */
    public static function getCurrentTable($tables, $cObj, $checkOnly = false)
    {
        foreach ($tables as $key => $table) {
            if (isset($tables[$key . '.']['enable'])) {
                $settings = $tables[$key . '.'];
                $uid = intval($cObj->getData($settings['enable']));

                if ($uid) {
                    if ($checkOnly) {
                        return true;
                    }
                    $data = [
                        'table' => $table,
                        'uid' => $uid,
                    ];

                    if (isset($settings['fallback.']) && count($settings['fallback.']) > 0) {
                        $data['fallback'] = $settings['fallback.'];
                    }

                    return $data;
                }
            }
        }

        // page
        $pagesTable = $GLOBALS['TSFE']->sys_language_uid > 0 ? 'pages_language_overlay' : 'pages';
        if (in_array($pagesTable, $tables)) {
            $pageUid = $GLOBALS['TSFE']->page['_PAGES_OVERLAY_UID'] ?: $GLOBALS['TSFE']->id;

            return [$pagesTable, $pageUid];
        }

        return false;
    }

    /**
     * @return bool|string meta tags, if available
     */
    public function getMetaTags($content, $conf)
    {
        // get table settings
        $tables = ConfigurationUtility::getPageTSconfig();

        if ($tables) {
            // get active table name und settings
            $tableSettings = $this->getCurrentTable($tables, $this->cObj);

            if ($tableSettings) {
                // get record
                $record = $this->getRecord($tableSettings);
                if (!is_array($record)) {
                    return false;
                }

                if ($record['_LOCALIZED_UID']) {
                    $tableSettings['uid'] = $record['_LOCALIZED_UID'];
                }
                // db meta
                $meta = $this->getMetaProperties($tableSettings);

                // db fallback
                if (isset($tableSettings['fallback'])) {
                    foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
                        if (empty($meta[$seoField]) && !empty($record[$fallbackField])) {
                            $meta[$seoField] = $record[$fallbackField];
                            if ($seoField == 'og_image' || $seoField == 'tw_image') {
                                $meta[$seoField] = [
                                    'field' => $fallbackField,
                                    'table' => $tableSettings['table'],
                                    'uid_foreign' => $tableSettings['uid']
                                ];
                            }
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
     * DB query to get the fallback properties
     *
     * @param $tableSettings
     *
     * @return bool
     */
    protected function getRecord($tableSettings)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableSettings['table']);

        $row = $queryBuilder->select('*')
            ->from($tableSettings['table'])
            ->where($queryBuilder->expr()->eq('uid',
                $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)))
            ->execute()
            ->fetch();

        if (is_array($row)) {
            $GLOBALS['TSFE']->sys_page->versionOL($tableSettings['table'], $row);
            $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                $tableSettings['table'],
                $row,
                $GLOBALS['TSFE']->sys_language_content,
                $GLOBALS['TSFE']->sys_language_contentOL
            );
        }

        return $row;
    }

    /**
     * DB query to get the current meta properties
     *
     * @param $tableSettings
     *
     * @return array
     */
    protected function getMetaProperties($tableSettings)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME_META);

        $res = $queryBuilder->select('*')
            ->from(self::TABLE_NAME_META)
            ->where(
                $queryBuilder->expr()->eq('uid_foreign',
                    $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableSettings['table']))
            )
            ->execute()->fetchAll();

        return isset($res[0]) ? $res[0] : [];
    }

    /**
     * render the meta tags
     *
     * @param $metaData
     *
     * @return string
     */
    protected function renderContent($metaData)
    {
        $metaTags = [];

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);

        /** @var \Clickstorm\CsSeo\Utility\TSFEUtility $tsfeUtility */
        $tsfeUtility = GeneralUtility::makeInstance(\Clickstorm\CsSeo\Utility\TSFEUtility::class, $GLOBALS['TSFE']->id);
        $pluginSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.'];

        // get active table and uid
        $tables = ConfigurationUtility::getPageTSconfig();
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $currentItemConf = self::getCurrentTable($tables, $cObj);

        // possible args: metaData, l10nItems, currentLanguageUid
        $defaultArgs = [];
        $signalSlotDispatcher->dispatch(__CLASS__, 'beforeMetaTagsCreatedForRecords',
            [
                &$defaultArgs,
                [
                    'currentItemConf' => $currentItemConf,
                    'metaData' => $metaData,
                    'pluginSettings' => $pluginSettings

                ],
                $this
            ]);

        if (is_array($defaultArgs['metaData'])) {
            $metaData = array_merge($metaData, $defaultArgs['metaData']);
        }

        $l10nItems = $defaultArgs['l10nItems'] ?: $this->getAllLanguagesFromItem($currentItemConf['table'],
            $currentItemConf['uid']);

        $currentLanguageUid = $defaultArgs['currentLanguageUid'] ?: $GLOBALS['TSFE']->sys_language_uid;

        // start to render metaTags
        $title = $metaData['title'];

        // title
        if ($title) {
            // update title for indexed search
            $GLOBALS['TSFE']->indexedDocTitle = $title;

            // add suffix or prefix
            $title = $tsfeUtility->getFinalTitle($metaData['title'], $metaData['title_only']);
        } else {
            // fallback to page title
            $pageTitleFunc = GeneralUtility::makeInstance(PageTitle::class);
            $title = $pageTitleFunc->render('', []);
        }

        $metaTags['title'] = '<title>' . $this->escapeContent($title) . '</title>';

        // description
        $metaTags['description'] = $this->printMetaTag('description', $this->escapeContent($metaData['description']));

        // hreflang & canonical
        $typoLinkConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['currentUrl.']['typolink.'];
        unset($typoLinkConf['parameter.']);
        $typoLinkConf['parameter'] = $GLOBALS['TSFE']->id;

        // canonical
        $canonicalTypoLinkConf = [];
        if ($metaData['canonical']) {
            $canonicalTypoLinkConf['parameter'] = $metaData['canonical'];
            $canonicalTypoLinkConf['forceAbsoluteUrl'] = 1;
        } else {
            $canonicalTypoLinkConf = $typoLinkConf;

            // if a fallback is shown, set canonical to the language of the ordered item
            if (!in_array($currentLanguageUid, $l10nItems)) {
                unset($canonicalTypoLinkConf['additionalParams.']['append.']['data']);
                $lang = $this->getLanguageFromItem($currentItemConf['table'], $currentItemConf['uid']);
                if ($lang < 0) {
                    $lang = 0;
                }
                $canonicalTypoLinkConf['additionalParams.']['append.']['value'] = $lang;
            }
        }
        $canonical = $this->cObj->typoLink_URL($canonicalTypoLinkConf);

        if (!$metaData['no_index']) {
            $metaTags['canonical'] = '<link rel="canonical" href="' . $canonical . '" />';
        }

        // index
        if ($metaData['no_index'] || $metaData['no_follow']) {
            $indexStr = $metaData['no_index'] ? 'noindex' : 'index';
            $indexStr .= ',';
            $indexStr .= $metaData['no_follow'] ? 'nofollow' : 'follow';
            $metaTags['robots'] = $this->printMetaTag('robots', $indexStr);
        } else {
            $metaTags['robots'] = $this->printMetaTag('robots', 'index,follow');
        }

        // hreflang
        // if the item for the current language uid exists and
        // the item is not set to no index and
        // the item points not to another page as canonical and
        // the TS setting hreflang.enabled is set to 1
        if (in_array(
                $currentLanguageUid,
                $l10nItems
            )
            && !$metaData['no_index']
            && !$metaData['canonical']
            && $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['enable']
        ) {
            $langIds = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['ids']);
            $langKeys = explode(",", $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.']['hreflang.']['keys']);

            $hreflangTypoLinkConf = $typoLinkConf;
            $metaTags['hreflang'] = '';

            foreach ($langIds as $key => $langId) {
                // set hreflang only for languages of the TS setup and if the language is also localized for the item
                // if the language doesn't exist for the item and a fallback language is shown, the hreflang is not set and the canonical points to the fallback url
                if (in_array($langId, $l10nItems)) {
                    unset($hreflangTypoLinkConf['additionalParams.']['append.']['data']);
                    $hreflangTypoLinkConf['additionalParams.']['append.']['value'] = $langId;
                    $hreflangUrl = $this->cObj->typoLink_URL($hreflangTypoLinkConf);
                    $metaTags['hreflang'] .= '<link rel="alternate" hreflang="'
                        . $langKeys[$key]
                        . '" href="'
                        . $hreflangUrl
                        . '" />';
                }
            }
        }

        // og:title
        $ogTitle = $metaData['og_title'] ?: $title;
        $metaTags['og:title'] = $this->printMetaTag('og:title', $this->escapeContent($ogTitle), 1);

        // og:description
        $ogDescription = $metaData['og_description'] ?: $metaData['description'];
        $metaTags['og:description'] = $this->printMetaTag('og:description', $this->escapeContent($ogDescription), 1);

        // og:image
        $ogImageURL = $pluginSettings['social.']['defaultImage'];

        if ($metaData['og_image']) {
            $ogImageURLFromRecord = $this->getImageOrFallback('og_image', $metaData);
            if ($ogImageURLFromRecord) {
                $ogImageURL = $ogImageURLFromRecord;
            }
        }

        if ($ogImageURL) {
            $finalOgImageURL = $this->getScaledImagePath(
                $ogImageURL,
                $pluginSettings['social.']['openGraph.']['image.']
            );
            $metaTags['og:image'] = $this->printMetaTag('og:image', $finalOgImageURL, 1);
        }

        // og:type
        if ($pluginSettings['social.']['openGraph.']['type']) {
            $metaTags['og:type'] = $this->printMetaTag('og:type', $pluginSettings['social.']['openGraph.']['type'], 1);
        }

        // og:url
        $metaTags['og:url'] = $this->printMetaTag('og:url', $canonical, 1);

        // og:locale
        $ogLocale = strstr($GLOBALS['TSFE']->config['config']['locale_all'], '.', true);
        $metaTags['og:locale'] = $this->printMetaTag('og:locale', $ogLocale, 1);

        // og:site_name
        $metaTags['og:site_name'] = $this->printMetaTag('og:site_name',
            $this->escapeContent($GLOBALS['TSFE']->tmpl->sitetitle), 1);

        // twitter title
        if ($metaData['tw_title']) {
            $metaTags['twitter:title'] = $this->printMetaTag('twitter:title',
                $this->escapeContent($metaData['tw_title']));
        }

        // twitter description
        if ($metaData['tw_description']) {
            $metaTags['twitter:description'] = $this->printMetaTag('twitter:description',
                $this->escapeContent($metaData['tw_description']));
        }

        // twitter image and type
        $twImageURL = '';
        if ($metaData['tw_image'] || $metaData['og_image']) {
            if ($metaData['tw_image']) {
                $twImageURL = $this->getImageOrFallback('tw_image', $metaData);
            } else {
                $twImageURL = $ogImageURL;
            }
        }

        if ($twImageURL) {
            $metaTags['twitter:card'] = $this->printMetaTag('twitter:card', 'summary_large_image');
        } else {
            $twImageURL =
                $pluginSettings['social.']['twitter.']['defaultImage'] ?: $pluginSettings['social.']['defaultImage'];
            $metaTags['twitter:card'] = $this->printMetaTag('twitter:card', 'summary');
        }

        if ($twImageURL) {
            $finalTwImageURL = $this->getScaledImagePath($twImageURL, $pluginSettings['social.']['twitter.']['image.']);
            $metaTags['twitter:image'] = $this->printMetaTag('twitter:image', $finalTwImageURL);
        }

        // twitter:creator
        $metaTags['twitter:creator'] = $this->printMetaTag(
            'twitter:creator',
            $this->escapeContent($metaData['tw_creator'] ?: $pluginSettings['social.']['twitter.']['creator'])
        );

        // twitter:site
        $metaTags['twitter:site'] = $this->printMetaTag(
            'twitter:site',
            $this->escapeContent($metaData['tw_site'] ?: $pluginSettings['social.']['twitter.']['site'])
        );

        $args = [
            'currentItemConf' => $currentItemConf,
            'l10nItems' => $l10nItems,
            'metaData' => $metaData,
            'pluginSettings' => $pluginSettings,
            'typoLinkConf' => $typoLinkConf
        ];

        $signalSlotDispatcher->dispatch(__CLASS__, 'afterMetaTagsCreatedForRecords',
            [&$metaTags, $args, $this]);

        return implode("\n", $metaTags);
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return array
     */
    protected function getAllLanguagesFromItem($table, $uid)
    {
        $languageIds = [];
        if (!isset($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']) || !isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])) {
            return $languageIds;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $pointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        $allItems = $queryBuilder->select($languageField)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq($pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->orWhere(
                $queryBuilder->expr()->eq('uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();

        foreach ($allItems as $item) {
            $languageIds[] = $item[$languageField];
        }

        return $languageIds;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function escapeContent($content)
    {
        return htmlentities(preg_replace('/\s\s+/', ' ', preg_replace('#<[^>]+>#', ' ', $content)),
            ENT_COMPAT, ini_get("default_charset"), false);
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $property
     *
     * @return string
     */
    protected function printMetaTag($name, $value, $property = false)
    {
        if (empty($value)) {
            return '';
        }

        $propertyString = $property ? 'property' : 'name';

        return '<meta ' . $propertyString . '="' . $name . '" content="' . $value . '" />';
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return int
     */
    protected function getLanguageFromItem($table, $uid)
    {
        if($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

            $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('uid',
                        $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
                )
                ->execute()
                ->fetchAll();

            return $items[0]['sys_language_uid'];
        }
        return 0;
    }

    /**
     * @param string $field
     * @param array $meta
     *
     * @return string the image path
     */
    protected function getImageOrFallback($field, $meta)
    {
        $params = [];
        if (is_array($meta[$field])) {
            $params['table'] = $meta[$field]['table'];
            $params['field'] = $meta[$field]['field'];
            $params['uid'] = $meta[$field]['uid_foreign'];
        } else {
            $params['table'] = self::TABLE_NAME_META;
            $params['field'] = 'tx_csseo_' . $field;
            $params['uid'] = $meta['uid'];
        }

        $image = DatabaseUtility::getFile($params['table'], $params['field'], $params['uid']);
        if ($image) {
            return $image->getPublicUrl();
        }
    }

    /**
     * Return an URL to the scaled image
     *
     * @param string $originalFile uid or path of the file
     * @param array $imageSize width and height as keys
     *
     * @return string
     */
    protected function getScaledImagePath($originalFile, $imageSize)
    {
        $conf = [
            'file' => $originalFile,
            'file.' => [
                'height' => $imageSize['height'],
                'width' => $imageSize['width']
            ]
        ];
        $imgUri = $this->cObj->cObjGetSingle('IMG_RESOURCE', $conf);
        $conf = [
            'parameter' => $imgUri,
            'forceAbsoluteUrl' => 1
        ];

        return $this->cObj->typoLink_URL($conf);
    }

    /**
     * return the social media image for pages
     *
     * @param  string          Empty string (no content to process)
     * @param  array           TypoScript configuration
     * @return integer         uid of the file
     */
    public function getSocialMediaImage($p1, $p2)
    {
        if ($GLOBALS['TSFE']->page['_PAGES_OVERLAY']) {
            $image = DatabaseUtility::getFile('pages_language_overlay', $p2['field'],
                $GLOBALS['TSFE']->page['_PAGES_OVERLAY_UID']);
            if (!empty($image)) {
                return $image->getUid();
            }
        }
        $image = DatabaseUtility::getFile('pages', $p2['field'], $GLOBALS['TSFE']->id);
        if (!empty($image)) {
            return $image->getUid();
        }
    }
}
