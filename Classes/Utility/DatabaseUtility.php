<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *  (c) 2017 Georg Ringer
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

/**
 * Access to the Database
 *
 * Class DatabaseUtility
 */
class DatabaseUtility
{

    /**
     * @param $table
     *
     * @return array
     */
    public static function getRecords($table, $pid = 0, $sortByLabel = false)
    {
        $items = [];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $queryBuilder
            ->select('*')
            ->from($table);

        if ($pid) {
            $queryBuilder->where($queryBuilder->expr()->eq('pid',
                $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)));
        }

        if ($sortByLabel && $GLOBALS['TCA'][$table]['ctrl']['label']) {
            $queryBuilder->orderBy($GLOBALS['TCA'][$table]['ctrl']['label'], 'ASC');
        } elseif ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
            $queryBuilder->orderBy($GLOBALS['TCA'][$table]['ctrl']['tstamp'], 'DESC');
        }

        $res = $queryBuilder->execute();

        while ($row = $res->fetch()) {
            $items[$row['uid']] = $row[$GLOBALS['TCA'][$table]['ctrl']['label']] . ' [' . $row['uid'] . ']';
        }

        return $items;
    }

    /**
     * @param $table
     *
     * @return array
     */
    public static function getPageLanguageOverlays($uid)
    {
        $items = [];
        $table = 'pages';
        $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $res = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    $tcaCtrl['transOrigPointerField'],
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->orderBy($tcaCtrl['languageField'])
            ->execute();

        while ($row = $res->fetch()) {
            $items[$row[$tcaCtrl['languageField']]] = $row;
        }

        return $items;
    }

    /**
     * Returns an image file for the given field and uid
     *
     * @param string $table
     * @param string $field
     * @param string $uid
     *
     * @return File|null
     */
    public static function getFile($table, $field, $uid)
    {
        /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(
            FileRepository::class
        );

        $fileObjects = $fileRepository->findByRelation(
            $table,
            $field,
            $uid
        );

        if ($fileObjects[0]) {
            return $fileObjects[0]->getOriginalFile();
        }
    }

    public static function getImageWithEmptyAlt(
        int $storage,
        string $identifier,
        $includeSubfolders = true,
        $countAll = false,
        $includeImagesWithAlt = false
    ) {
        $tableName = 'sys_file';
        $joinTableName = 'sys_file_metadata';

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($includeSubfolders) {
            $folderExpression = $queryBuilder->expr()->like('file.identifier',
                $queryBuilder->createNamedParameter($identifier . '%', \PDO::PARAM_STR));
        } else {
            // get folder hash
            $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
            $folder = $resourceFactory->getFolderObjectFromCombinedIdentifier($storage . ':' . $identifier);
            $folderExpression = $queryBuilder->expr()->eq('file.folder_hash',
                $queryBuilder->createNamedParameter($folder->getHashedIdentifier(), \PDO::PARAM_STR));
        }

        $queryBuilder
            ->select('file.*')
            ->from($tableName, 'file')
            ->leftJoin(
                'file',
                $joinTableName,
                'meta',
                $queryBuilder->expr()->eq(
                    'meta.file', $queryBuilder->quoteIdentifier('file.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->eq('file.type',
                    $queryBuilder->createNamedParameter(File::FILETYPE_IMAGE, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('file.storage',
                    $queryBuilder->createNamedParameter($storage, \PDO::PARAM_INT)),
                $folderExpression
            );


        if (!$includeImagesWithAlt) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('meta.alternative',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)),
                    $queryBuilder->expr()->isNull('meta.alternative')
                )
            );
        }

        if ($countAll) {
            $queryBuilder->count('file.uid');
        } else {
            $queryBuilder->setMaxResults(1);
        }

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Find all ids from given ids and level, copied from the news extension by Georg Ringer
     *
     * @param string $pidList comma separated list of ids
     * @param int $recursive recursive levels
     * @return string comma separated list of ids
     */
    public static function extendPidListByChildren($pidList = '', $recursive = 0)
    {
        $recursive = (int)$recursive;
        if ($recursive <= 0) {
            return $pidList;
        }

        $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);
        $recursiveStoragePids = $pidList;
        $storagePids = GeneralUtility::intExplode(',', $pidList);
        foreach ($storagePids as $startPid) {
            if ($startPid >= 0) {
                $pids = $queryGenerator->getTreeList($startPid, $recursive, 0, 1);
                if (strlen($pids) > 0) {
                    $recursiveStoragePids .= ',' . $pids;
                }
            }
        }

        return GeneralUtility::uniqueList($recursiveStoragePids);
    }

    /**
     * Migrate fields from one column to another of a table
     *
     * @param array $columnNamesToMigrate
     * @param string $table
     */
    public static function migrateColumnNames($columnNamesToMigrate, $table)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        foreach ($columnNamesToMigrate as $oldCol => $newCol) {
            $queryBuilder
                ->update($table, 'u')
                ->where(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq($newCol, $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)),
                        $queryBuilder->expr()->isNull($newCol)
                    )
                )
                ->set('u.' . $newCol, $queryBuilder->quoteIdentifier('u.' . $oldCol), false)
                ->execute();

            $queryBuilder->resetQueryParts();
        }
    }

    /**
     * Migrate fields from one column to another of a table
     *
     * @param array $content
     * @param string $column
     * @param string $table
     * @param string $tableRelated
     */
    public static function migrateRelatedColumnContent($content, $column, $table, $tableRelated)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        foreach ($content as $oldContent => $newContent) {
            $queryBuilder
                ->update($table, 'u')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq($column, $queryBuilder->createNamedParameter($oldContent)),
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableRelated))
                    )
                )
                ->set('u.' . $column, $queryBuilder->createNamedParameter($newContent), false)
                ->execute();

            $queryBuilder->resetQueryParts();
        }
    }

    public static function getLanguagesInBackend(int $pageId = 0): array
    {
        $languages[0] = 'Default';

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');

        $res = $queryBuilder->select('*')
            ->from('sys_language')
            ->orderBy('title')
            ->execute();

        while ($lRow = $res->fetch()) {
            if (GlobalsUtility::getBackendUser()->checkLanguageAccess($lRow['uid'])) {
                $languages[$lRow['uid']] = $lRow['hidden'] ? '(' . $lRow['title'] . ')' : $lRow['title'];
            }
        }

        // Setting alternative default label:
        if ($pageId) {
            $modTSconfig = BackendUtility::getPagesTSconfig($pageId)['mod.']['SHARED.'] ?? [];
            if ($modTSconfig['properties']['defaultLanguageLabel']) {
                $languages[0] = $modTSconfig['properties']['defaultLanguageLabel'];
            }
        }

        return $languages;
    }

    /**
     * Fetch a single record
     *
     * @param string $table
     * @param int $uid
     * @param string $select
     *
     * @return mixed
     */
    public static function getRecord($table, $uid, $select = '*')
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        return $queryBuilder->select(...GeneralUtility::trimExplode(',', $select, true))
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
    }
}
