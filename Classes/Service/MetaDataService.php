<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\LanguageAspect;
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
     * @var ContentObjectRenderer
     */
    protected $cObj;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var AspectInterface|LanguageAspect|null
     */
    protected $languageAspect;

    public function __construct()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $context = clone GeneralUtility::makeInstance(Context::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, $context);
        $this->languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
    }

    public function getMetaData()
    {
        // check if metadata was already set
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'])) {
            return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'];
        }

        // get table settings
        $tables = ConfigurationUtility::getTablesToExtend();

        if ($tables !== []) {
            // get active table name und settings
            $tableSettings = $this->getCurrentTableConfiguration($tables, $this->cObj);

            if ($tableSettings) {
                // get record
                $record = $this->getRecord($tableSettings);
                if (!is_array($record)) {
                    return null;
                }

                if (!empty($record['_LOCALIZED_UID'])) {
                    $tableSettings['uid'] = $record['_LOCALIZED_UID'];
                }
                // db meta
                $metaData = $this->getMetaProperties($tableSettings);
                $metaData['__uid'] = $tableSettings['uid'];

                // db fallback
                if (isset($tableSettings['fallback'])) {
                    foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
                        if (empty($metaData[$seoField])) {
                            if (!empty($record[$fallbackField])) {
                                $metaData[$seoField] = $record[$fallbackField];
                                if ($seoField === 'og_image' || $seoField === 'tw_image') {
                                    $metaData[$seoField] = [
                                        'field' => $fallbackField,
                                        'table' => $tableSettings['table'],
                                        'uid_foreign' => $tableSettings['uid'],
                                    ];
                                }
                            } // check for double slash, if so multiple fallback fields are defined, the first with content will be used
                            elseif (strpos($fallbackField, '//') !== false) {
                                foreach (GeneralUtility::trimExplode('//', $fallbackField) as $possibleFallbackField) {
                                    if (!empty($record[$possibleFallbackField])) {
                                        $metaData[$seoField] = $record[$possibleFallbackField];
                                    }
                                }
                            } // check for curly brackets, if so, replace the brackets with their corresponding metaData $seoField
                            elseif (preg_match('/{([^}]+)}/', $fallbackField)) {
                                $curlyBracketSeoField = $fallbackField;
                                $matches = [];
                                preg_match_all('/{([^}]+)}/', $fallbackField, $matches);
                                $matchesWithCurlyBrackets = $matches[0];
                                $matchesWithoutCurlyBrackets = $matches[1];
                                foreach ($matchesWithCurlyBrackets as $key => $matchWithCurlyBracket) {
                                    $recordField = $matchesWithoutCurlyBrackets[$key];
                                    $curlyBracketSeoField = str_replace(
                                        $matchWithCurlyBracket,
                                        $record[$recordField],
                                        $curlyBracketSeoField
                                    );
                                }
                                $metaData[$seoField] = $curlyBracketSeoField;
                            }
                        }
                    }
                }

                // set metaData in Globals
                $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'] = $metaData;

                return $metaData;
            }
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'] = false;

        return null;
    }

    /**
     * Check if extension detail view or page properties should be used
     *
     * @param $tables
     * @param ContentObjectRenderer $cObj
     * @param bool $checkOnly
     *
     * @return array|bool
     */
    public static function getCurrentTableConfiguration($tables, $cObj, $checkOnly = false)
    {
        foreach ($tables as $tableName => $tableSettings) {
            if (isset($tableSettings['enable'])) {
                $uid = (int)($cObj->getData($tableSettings['enable']));

                if ($uid !== 0) {
                    if ($checkOnly) {
                        return true;
                    }
                    $data = [
                        'table' => $tableName,
                        'uid' => $uid,
                    ];

                    if (isset($tableSettings['fallback']) && count($tableSettings['fallback']) > 0) {
                        $data['fallback'] = $tableSettings['fallback'];
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
     * @return array|null
     */
    protected function getRecord($tableSettings)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableSettings['table']);

        $row = $queryBuilder->select('*')
            ->from($tableSettings['table'])->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
        ))->executeQuery()
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
            ->from(self::TABLE_NAME_META)->where($queryBuilder->expr()->eq(
            'uid_foreign',
            $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
        ), $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableSettings['table'])))->executeQuery()->fetchAll();

        return isset($res[0]) ? $res[0] : [];
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
