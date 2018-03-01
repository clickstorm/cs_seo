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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\DatabaseUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Generates the sitemap.xml
 *
 * Class Sitemap
 *
 * @package Clickstorm\CsSeo\UserFunc
 */
class Sitemap
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var TypoScriptFrontendController
     */
    protected $tsfe;


    public function main()
    {
        $this->tsfe = $this->getTypoScriptFrontendController();
        $this->pageRepository = $this->tsfe->sys_page;

        // set TypoScript settings and parse them for Fluid
        $this->setSettings($this->parseSettings($this->tsfe->tmpl->setup['plugin.']['tx_csseo.']['sitemap.']));

        // init fluid templates
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setFormat('xml');
        $this->view->getRequest()->setControllerExtensionName('cs_seo');
        $this->view->setLayoutRootPaths($this->settings['view']['layoutRootPaths']);
        $this->view->setPartialRootPaths($this->settings['view']['partialRootPaths']);
        $this->view->setTemplateRootPaths($this->settings['view']['templateRootPaths']);

        // get UID of current page if rootPid = 0
        if(empty($this->settings['pages']['rootPid'])) {
            $this->settings['pages']['rootPid'] = $GLOBALS['TSFE']->id;
        }

        // switch view
        switch (GeneralUtility::_GP('tx_csseo_view')) {
            // sitemap for pages
            case 'pages':
                $this->view->setTemplate('Pages');
                $settings = $this->settings['pages'];

                // first get the root page
                $rootPage = $this->tsfe->sys_page->getPage($settings['rootPid']);

                // remove doktype exlude
                $hideDelArray = explode('AND', $this->tsfe->sys_page->where_hid_del);
                foreach ($hideDelArray as $key => $entry) {
                    if (strpos($entry, 'doktype') !== false) {
                        unset($hideDelArray[$key]);
                    }
                }
                $this->tsfe->sys_page->where_hid_del = implode('AND', $hideDelArray);

                $this->view->assignMultiple(
                    [
                        'settings' => $settings,
                        'lang' => $this->tsfe->sys_language_uid,
                        'pageUid' => $rootPage['uid']
                    ]
                );
                break;
            // sitemap for extensions
            case 'extension':
                $this->view->setTemplate('Extension');
                $extName = GeneralUtility::_GP('ext');
                if ($extName) {
                    $extConf = $this->settings['extensions'][$extName];
                    if ($extConf) {
                        if (!empty($extConf['getRecordsUserFunction'])) {
                            $params = [
                                'extConf' => $extConf
                            ];
                            $records = GeneralUtility::callUserFunction($extConf['getRecordsUserFunction'], $params, $this);
                        } else {
                            $records = $this->getRecords($extConf);
                        }
                        if (is_array($records) && count($records) > 0) {
                            $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                            foreach ($records as $key => $record) {
                                $detailPid = $record['detailPid'] ?: $extConf['detailPid'];
                                $typoLinkConf = [
                                    'parameter' => $detailPid,
                                    'forceAbsoluteUrl' => 1
                                ];
                                $typoLinkConf['useCacheHash'] = !empty($extConf['useCacheHash']);
                                $typoLinkConf['additionalParams'] =
                                    '&' . $extConf['additionalParams'] . '=' . $record['uid'];
                                if ($record['lang']) {
                                    $typoLinkConf['additionalParams'] .= '&L=' . $this->tsfe->sys_language_uid;
                                }
                                $records[$key]['loc'] = $cObject->typoLink_URL($typoLinkConf);
                            }
                        }

                        $this->view->assignMultiple(
                            [
                                'extConf' => $extConf,
                                'records' => $records
                            ]
                        );
                    }
                }
                break;
            // list all sitemaps
            default:
                $this->view->setTemplate('ListAll');
                $this->view->assign('settings', $this->settings);
        }


        return $this->beautifyXML($this->view->render());
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * parse the TypoScript settings for Fluid
     *
     * @param $settings
     *
     * @return array
     */
    protected function parseSettings($settings)
    {
        $parsedSettings = [];
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                $key = rtrim($key, '.');
                if (!is_array($value)) {
                    $parsedSettings[$key] = $value;
                } else {
                    $parsedSettings[$key] = $this->parseSettings($value);
                }
            }
        }

        return $parsedSettings;
    }

    /**
     * @param array $pages
     * @param array $newPages
     *
     * @return array
     */
    protected function getSubPages($pageUid)
    {
        $subPages = $this->tsfe->sys_page->getMenu(
            $pageUid,
            '*',
            'sorting',
            ''
        );

        foreach ($subPages as $subPage) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $subPages,
                $this->getSubPages($subPage['uid'])
            );
        }

        return $subPages;
    }

    /**
     * @param array $extConf
     *
     * @return bool|array
     */
    protected function getRecords($extConf)
    {
        $db = $this->getDatabaseConnection();
        $table = $extConf['table'];
        $where = '';
        $from = $extConf['table'];
        $select = $table . '.uid';

        $constraints = [];
        $lang = $this->getTypoScriptFrontendController()->sys_language_uid;
        $tca = $GLOBALS['TCA'][$extConf['table']];

        // storage
        if ($extConf['storagePid']) {
            $storagePid = $extConf['storagePid'];
            $recursive = (int)$extConf['recursive'];
            if($recursive > 0) {
                $storagePid = \Clickstorm\CsSeo\Utility\DatabaseUtility::extendPidListByChildren($storagePid, $recursive);
            }
            $constraints[] = $table . '.pid IN (' . $storagePid . ')';
        }

        if ($tca) {
            // lang
            $languageField = $tca['ctrl']['languageField'];
            if ($languageField) {
                $constraints[] = $table . '.' . $languageField . ' IN (' . $lang . ',-1)';
                $select .= ', ' . $table . '.' . $languageField . ' AS lang';
            }

            // lastmod
            if ($tca['ctrl']['tstamp']) {
                $select .= ', ' . $table . '.' . $tca['ctrl']['tstamp'] . ' AS lastmod';
            }

            // no index
            if ($tca['columns']['tx_csseo']) {
                $from .= ' LEFT JOIN tx_csseo_domain_model_meta ON '
                    . $table
                    . '.uid = tx_csseo_domain_model_meta.uid_foreign';
                $constraints[] = '(' . $table . '.tx_csseo = 0 OR
        	 (tx_csseo_domain_model_meta.tablenames = ' . $db->fullQuoteStr($table, $table) . ' AND
        	 tx_csseo_domain_model_meta.no_index = 0))';
            }
        }

        // categories
        if ($extConf['categoryField']) {
            $catField = $extConf['categoryField'];
            if($extConf['categories']) {
                $constraints[] = $catField . ' IN (' . $extConf['categories'] . ')';
            }

            if ($extConf['categoryTable'] && $extConf['categoryDetailPidField']) {
                $catTable = $extConf['categoryTable'];
                $from .= ' LEFT JOIN ' . $catTable . ' ON ' . $table . '.' . $catField . ' = ' . $catTable . '.uid';
                $select .= ', ' . $catTable . '.' . $extConf['categoryDetailPidField'] . ' AS detailPid';
            }
        }

        if ($extConf['categoryMMTable']) {
            $catMMTable = $extConf['categoryMMTable'];
            $from .= ' LEFT JOIN ' . $catMMTable . ' ON ' . $table . '.uid = ' . $catMMTable . '.uid_foreign';
            if($extConf['categories']) {
                $constraints[] = $catMMTable . '.uid_local IN (' . $extConf['categories'] . ')';
                if ($extConf['categoryMMTablename']) {
                    $constraints[] = $catMMTable . '.tablenames = ' . $db->fullQuoteStr($table, $table);
                }
                if ($extConf['categoryMMFieldname']) {
                    $constraints[] = $catMMTable . '.fieldname = ' . $db->fullQuoteStr(
                            $extConf['categoryMMFieldname'],
                            $table
                        );
                }
            }

            if ($extConf['categoryTable'] && $extConf['categoryDetailPidField']) {
                $catTable = $extConf['categoryTable'];
                $from .= ' LEFT JOIN ' . $catTable . ' ON ' . $catMMTable . '.uid_local = ' . $catTable . '.uid';
                $select .= ', ' . $catTable . '.' . $extConf['categoryDetailPidField'] . ' AS detailPid';
            }
        }

        if ($extConf['additionalWhereClause']) {
            $constraints[] = $extConf['additionalWhereClause'];
        }

        if (count($constraints)) {
            $where .= implode($constraints, ' AND ');
        } else {
            $where = '1=1';
        }

        if ($tca) {
            $where .= $this->pageRepository->enableFields(
                $extConf['table']
            );
        }

        return $db->exec_SELECTgetRows(
            $select,
            $from,
            $where,
            $table . '.uid'
        );
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param $string XML String
     *
     * @return string
     */
    protected function beautifyXML($string)
    {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($string);
        $dom->formatOutput = true;

        return $dom->saveXml();
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
