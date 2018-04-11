<?php

namespace Clickstorm\CsSeo;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;

/**
 * Update class for the extension manager.
 *
 * @package TYPO3
 * @subpackage tx_csseo
 */
class ext_update
{

    const FOLDER_CATEGORY_IMAGES = '/_migrated/news_categories';

    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = [];

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();

        return $this->generateOutput();
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->migrateFromMetaseo();
        $this->migrateFromSeoBasics();
    }

    /**
     * Check if metaseo was installed and then transfer the properties from pages and pages_language_overlay
     *
     * @return void
     */
    protected function migrateFromMetaseo()
    {
        // check if tx_metaseo fields exists
        if (!array_key_exists('tx_metaseo_pagetitle', $this->getPageColumns())) {
            $status = FlashMessage::NOTICE;
            $title = 'metaseo not found';
            $message = 'No MetaSeo properties, so no update needed';
            $this->messageArray[] = [$status, $title, $message];

            return;
        }

        // update title only if absolute title
        $this->updateTitleOnly('tx_metaseo_pagetitle', 'pages');

        // migrate pages
        $fieldsToMigrate = [
            'tx_metaseo_pagetitle' => 'tx_csseo_title',
            'tx_metaseo_pagetitle_rel' => 'tx_csseo_title',
            'tx_metaseo_canonicalurl' => 'tx_csseo_canonical',
            'tx_metaseo_is_exclude' => 'tx_csseo_no_index',
        ];

        $this->migrateFields($fieldsToMigrate, 'pages');

        // update title only if absolute title
        $this->updateTitleOnly('tx_metaseo_pagetitle', 'pages_language_overlay');

        // migrate pages_language_overlay
        $fieldsToMigrate = [
            'tx_metaseo_pagetitle' => 'tx_csseo_title',
            'tx_metaseo_pagetitle_rel' => 'tx_csseo_title',
        ];

        $this->migrateFields($fieldsToMigrate, 'pages_language_overlay');

        /**
         * Finished migration from metaseo
         */
        $message = 'Title, Canonical and NoIndex are migrated. Run DB compare in the install tool to remove the fields from metaseo and run the DB check to update the reference index.';
        $status = FlashMessage::OK;
        $title = 'Migrated all metaseo fields!';
        $this->messageArray[] = [$status, $title, $message];
    }

    /**
     * returns an array with all fields of the table pages
     *
     * @return mixed
     */
    protected function getPageColumns()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->getSchemaManager()
            ->listTableColumns('pages');
    }

    /**
     * migrate the title only field, if absolute title-tag is set
     * @param string $field
     * @param string $table
     */
    protected function updateTitleOnly($field, $table)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $queryBuilder
            ->update($table, 'u')
            ->where(
                $queryBuilder->expr()->gt($field, '\'\'')
            )
            ->set('u.tx_csseo_title_only', 1)
            ->execute();
    }

    /**
     * Migrate fields from one column to another of a table
     *
     * @param array $fieldsToMigrate
     * @param string $table
     *
     * @return void
     */
    protected function migrateFields($fieldsToMigrate, $table)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        foreach ($fieldsToMigrate as $oldField => $newField) {

            $queryBuilder
                ->update($table, 'u')
                ->where(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq($newField, ''),
                        $queryBuilder->expr()->isNull($newField)
                    )
                )
                ->set('u.' . $newField, $queryBuilder->createNamedParameter('u.' . $oldField))
                ->execute();
        }
    }

    /**
     * Check if seo_basics was installed and then transfer the properties from pages and pages_language_overlay
     *
     * @return void
     */
    protected function migrateFromSeoBasics()
    {

        // check if seo_basics fields exists
        if (!array_key_exists('tx_seo_titletag', $this->getPageColumns())) {
            $status = FlashMessage::NOTICE;
            $title = 'seo_basics not found';
            $message = 'No seo_basics properties, so no update needed';
            $this->messageArray[] = [$status, $title, $message];

            return;
        }

        // migrate pages
        $fieldsToMigrate = [
            'tx_seo_titletag' => 'tx_csseo_title',
            'tx_seo_canonicaltag' => 'tx_csseo_canonical',
        ];

        $this->migrateFields($fieldsToMigrate, 'pages');
        $this->migrateFields($fieldsToMigrate, 'pages_language_overlay');

        /**
         * Finished migration from seo_basics
         */
        $message = 'Title and Canonical are migrated. Run DB compare in the install tool to remove the fields from metaseo and run the DB check to update the reference index.';
        $status = FlashMessage::OK;
        $title = 'Migrated all seo_basics fields!';
        $this->messageArray[] = [$status, $title, $message];
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();

        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]);
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }

        return $defaultFlashMessageQueue->renderFlashMessages();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     * @todo find a better way to determine if update is needed or not.
     */
    public function access()
    {
        return true;
    }

}
