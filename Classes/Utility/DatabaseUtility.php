<?php

namespace Clickstorm\CsSeo\Utility;

use Clickstorm\CsSeo\Domain\Model\Dto\FileModuleOptions;
use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
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
    public static function getRecords(string $table, int $pid = 0, bool $sortByLabel = false): array
    {
        $items = [];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $queryBuilder
            ->select('*')
            ->from($table);

        if ($pid) {
            $queryBuilder->where($queryBuilder->expr()->eq(
                'pid',
                $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
            ));
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

    public static function getPageLanguageOverlays(int $uid): array
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
            )->orderBy($tcaCtrl['languageField'])->executeQuery();

        while ($row = $res->fetch()) {
            $items[$row[$tcaCtrl['languageField']]] = $row;
        }

        return $items;
    }

    public static function getFile(string $table, string $field, int $uid): ?File
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(
            FileRepository::class
        );

        $fileObjects = $fileRepository->findByRelation(
            $table,
            $field,
            $uid
        );

        return isset($fileObjects[0]) ? $fileObjects[0]->getOriginalFile() : null;
    }

    public static function getImageWithEmptyAlt(
        FileModuleOptions $fileModuleOptions,
        bool              $countAll = false,
        bool              $includeImagesWithAlt = false,
        int               $offset = 0
    ): ?array
    {
        $tableName = 'sys_file';
        $joinTableName = 'sys_file_metadata';

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($fileModuleOptions->isIncludeSubfolders()) {
            $folderExpression = $queryBuilder->expr()->like(
                'file.identifier',
                $queryBuilder->createNamedParameter($fileModuleOptions->getIdentifier() . '%', Connection::PARAM_STR)
            );
        } else {
            $folderExpression = $queryBuilder->expr()->like(
                'file.identifier',
                $queryBuilder->createNamedParameter($fileModuleOptions->getIdentifier() . '%', Connection::PARAM_STR)
            );

            // get folder hash
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $folder = $resourceFactory->getFolderObjectFromCombinedIdentifier(
                $fileModuleOptions->getStorageUid() . ':' . $fileModuleOptions->getIdentifier()
            );
            $folderExpression = $queryBuilder->expr()->eq(
                'file.folder_hash',
                $queryBuilder->createNamedParameter($folder->getHashedIdentifier(), Connection::PARAM_STR)
            );
        }

        $queryBuilder
            ->select('file.*')
            ->from($tableName, 'file')
            ->leftJoin(
                'file',
                $joinTableName,
                'meta',
                $queryBuilder->expr()->eq(
                    'meta.file',
                    $queryBuilder->quoteIdentifier('file.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'file.type',
                    $queryBuilder->createNamedParameter(File::FILETYPE_IMAGE, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'file.storage',
                    $queryBuilder->createNamedParameter($fileModuleOptions->getStorageUid(), Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'file.missing',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $folderExpression,
                // always check the default language of sys_file_metadata
                $queryBuilder->expr()->in(
                    'meta.sys_language_uid',
                    $queryBuilder->createNamedParameter($queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                )
            );

        if (!empty($fileModuleOptions->getExcludedImageExtensions())) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn(
                    'file.extension',
                    $queryBuilder->createNamedParameter($fileModuleOptions->getExcludedImageExtensions(), ArrayParameterType::STRING)
                )
            );
        }

        if ($fileModuleOptions->isOnlyReferenced()) {
            $queryBuilder->join(
                'file',
                'sys_refindex',
                'refindex',
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('refindex.ref_table', $queryBuilder->createNamedParameter('sys_file')),
                    $queryBuilder->expr()->eq('refindex.ref_uid', $queryBuilder->quoteIdentifier('file.uid')),
                    $queryBuilder->expr()->neq('refindex.tablename', $queryBuilder->createNamedParameter('sys_file_metadata'))
                )
            )->groupBy('file.uid');
        }

        if (!$includeImagesWithAlt) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->or($queryBuilder->expr()->eq(
                    'meta.alternative',
                    $queryBuilder->createNamedParameter('', Connection::PARAM_STR)
                ), $queryBuilder->expr()->isNull('meta.alternative'))
            );
        }

        if ($countAll) {
            $queryBuilder->count('file.uid');
        } else {
            if ($offset > 0) {
                $queryBuilder->setFirstResult($offset);
            }
            $queryBuilder->setMaxResults(1);
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public static function getFileReferenceCount(int $fileUid): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_refindex');
        return (int)$queryBuilder
            ->count('*')
            ->from('sys_refindex')
            ->where(
                $queryBuilder->expr()->eq(
                    'ref_table',
                    $queryBuilder->createNamedParameter('sys_file')
                ),
                $queryBuilder->expr()->eq(
                    'ref_uid',
                    $queryBuilder->createNamedParameter($fileUid, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'tablename',
                    $queryBuilder->createNamedParameter('sys_file_metadata')
                )
            )
            ->executeQuery()
            ->fetchOne();
    }

    public static function getLanguagesInBackend(int $pageId = 0): array
    {
        $languages[0] = 'Default';

        if ($pageId === 0) {
            return $languages;
        }

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByRootPageId($pageId);
        } catch (SiteNotFoundException $exception) {
            return $languages;
        }

        foreach ($site->getAvailableLanguages(GlobalsUtility::getBackendUser()) as $language) {
            // @extensionScannerIgnoreLine
            $languages[$language->getLanguageId()] = $language->getTitle();
        }

        return $languages;
    }

    public static function getRecord(string $table, int $uid, string $select = '*'): ?array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        return $queryBuilder->select(...GeneralUtility::trimExplode(',', $select, true))
            ->from($table)->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
            ))->executeQuery()
            ->fetch();
    }
}
