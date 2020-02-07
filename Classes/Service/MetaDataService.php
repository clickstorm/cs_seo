<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
class MetaDataService
{
    const TABLE_NAME_META = 'tx_csseo_domain_model_meta';

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    /**
     * @var PageRepository
     */
    protected $pageRepository = null;

    /**
     * @var \TYPO3\CMS\Core\Context\AspectInterface|\TYPO3\CMS\Core\Context\LanguageAspect|null
     */
    protected $languageAspect = null;

    public function __construct()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, $this->context);
        $this->languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
    }

    public function getMetaData(): ?array
    {
        // check if metadata was already set
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData']) {
            return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'];
        }

        // get table settings
        $tables = ConfigurationUtility::getPageTSconfig();
        if ($tables) {
            // get active table name und settings
            $tableSettings = $this->getCurrentTableConfiguration($tables, $this->cObj);

            if ($tableSettings) {
                // get record
                $record = $this->getRecord($tableSettings);
                if (!is_array($record)) {
                    return null;
                }

                if ($record['_LOCALIZED_UID']) {
                    $tableSettings['uid'] = $record['_LOCALIZED_UID'];
                }
                // db meta
                $metaData = $this->getMetaProperties($tableSettings);

                // db fallback
                if (isset($tableSettings['fallback'])) {
                    foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
                        if (empty($metaData[$seoField]) && !empty($record[$fallbackField])) {
                            $metaData[$seoField] = $record[$fallbackField];
                            if ($seoField == 'og_image' || $seoField == 'tw_image') {
                                $metaData[$seoField] = [
                                    'field' => $fallbackField,
                                    'table' => $tableSettings['table'],
                                    'uid_foreign' => $tableSettings['uid']
                                ];
                            }
                        }
                    }
                }

                // set metaData in Globals
                $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'] = $metaData;

                return $metaData;
            }
        }

        return null;
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
    public static function getCurrentTableConfiguration($tables, $cObj, $checkOnly = false)
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
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
            ))
            ->execute()
            ->fetch();

        if (is_array($row)) {
            $this->pageRepository->versionOL($tableSettings['table'], $row);
            $row = $this->pageRepository->getRecordOverlay(
                $tableSettings['table'],
                $row,
                $this->languageAspect->getContentId(),
                $this->languageAspect->getLegacyLanguageMode()
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
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableSettings['table']))
            )
            ->execute()->fetchAll();

        return isset($res[0]) ? $res[0] : [];
    }
}
