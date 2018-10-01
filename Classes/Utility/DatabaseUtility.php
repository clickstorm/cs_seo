<?php

namespace Clickstorm\CsSeo\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;

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
 *
 * @package Clickstorm\CsSeo\Utility
 */
class DatabaseUtility
{

    /**
     * @param $table
     *
     * @return array
     */
    public static function getRecords($table)
    {
        $items = [];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $queryBuilder
            ->select('*')
            ->from($table);

        if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
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
                $queryBuilder->expr()->eq('pid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
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
     * @return \TYPO3\CMS\Core\Resource\File|null
     */
    public static function getFile($table, $field, $uid)
    {
        /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Resource\FileRepository::class
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

        $queryGenerator = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\QueryGenerator::class);
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
}
