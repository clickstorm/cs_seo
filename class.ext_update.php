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
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update class for the extension manager.
 *
 */
class ext_update
{
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
     * @throws \TYPO3\CMS\Core\Exception
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
     * Check if metaseo was installed and then transfer the properties from pages
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
            'tx_metaseo_pagetitle' => 'seo_title',
            'tx_metaseo_pagetitle_rel' => 'seo_title',
            'tx_metaseo_canonicalurl' => 'canonical_link',
            'tx_metaseo_is_exclude' => 'no_index',
        ];

        DatabaseUtility::migrateColumnNames($fieldsToMigrate, 'pages');

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
     * Check if seo_basics was installed and then transfer the properties from pages
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
            'tx_seo_titletag' => 'seo_title',
            'tx_seo_canonicaltag' => 'canonical_link',
        ];

        DatabaseUtility::migrateColumnNames($fieldsToMigrate, 'pages');

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
     * @throws \TYPO3\CMS\Core\Exception
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
                $messageItem[0]
            );
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
