<?php

namespace Clickstorm\CsSeo\Updates;

use Clickstorm\CsSeo\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

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
class PagesUpdater implements UpgradeWizardInterface
{
    public static $identifier = 'tx_csseo_pages';


    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return self::$identifier;
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Migrate EXT:cs_seo pages fields to core fields';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Before TYPO3 v9 some fields like the seo title or open graph tags were saved in cs_seo columns. Now'
            . 'fields are provided by the core.';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        $fieldsToMigrate = [
            'tx_csseo_title' => 'seo_title',
            'tx_csseo_canonical' => 'canonical_link',
            'tx_csseo_no_index' => 'no_index',
            'tx_csseo_no_follow' => 'no_follow',
            'tx_csseo_og_title' => 'og_title',
            'tx_csseo_og_description' => 'og_description',
            'tx_csseo_og_image' => 'og_image',
            'tx_csseo_tw_title' => 'twitter_title',
            'tx_csseo_tw_description' => 'twitter_description',
            'tx_csseo_tw_image' => 'twitter_image'
        ];

        DatabaseUtility::migrateColumnNames($fieldsToMigrate, 'pages');

        $content = [
            'tx_csseo_og_image' => 'og_image',
            'tx_csseo_tw_image' => 'twitter_image'
        ];

        DatabaseUtility::migrateRelatedColumnContent($content, 'fieldname', 'sys_file_reference', 'pages');

        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $tableColumns = $connection->getSchemaManager()->listTableColumns('pages');

        // Only proceed if section_frame field still exists
        return isset($tableColumns['tx_csseo_title']);
    }

    /**
     * Returns an array of class names of Prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}