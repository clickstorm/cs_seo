<?php
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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update class for the extension manager.
 *
 * @package TYPO3
 * @subpackage tx_news
 */
class ext_update {

	const FOLDER_CATEGORY_IMAGES = '/_migrated/news_categories';

	/**
	 * Array of flash messages (params) array[][status,title,message]
	 *
	 * @var array
	 */
	protected $messageArray = [];

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Main update function called by the extension manager.
	 *
	 * @return string
	 */
	public function main() {
		$this->processUpdates();
		return $this->generateOutput();
	}

	/**
	 * Called by the extension manager to determine if the update menu entry
	 * should by showed.
	 *
	 * @return bool
	 * @todo find a better way to determine if update is needed or not.
	 */
	public function access() {
		return TRUE;
	}

	/**
	 * The actual update function. Add your update task in here.
	 *
	 * @return void
	 */
	protected function processUpdates() {
		$this->migrateFromMetaseo();
	}
	/**
	 * Check if there are records in the old category table and transfer
	 * these to sys_category when needed
	 */
	protected function migrateFromMetaseo() {

		// check if tx_metaseo fields exists
		$metaseoTableFields = $this->databaseConnection->admin_get_fields('pages');
		if (!array_key_exists('tx_metaseo_pagetitle', $metaseoTableFields)) {
			$status = FlashMessage::NOTICE;
			$title = '';
			$message = 'Mo MetaSeo properties, so no update needed';
			$this->messageArray[] = [$status, $title, $message];
			return;
		}

		// migrate pages
		$fieldsToMigrate = [
			'tx_metaseo_pagetitle'        => 'tx_csseo_title',
			'tx_metaseo_canonicalurl'    => 'tx_csseo_canonical',
			'tx_metaseo_is_exclude' => 'tx_csseo_no_index',
		];

		$this->migrateFields($fieldsToMigrate, 'pages');

		// Page fields
		$fieldsToMigrate = [
			'tx_metaseo_pagetitle'        => 'tx_csseo_title',
		];

		$this->migrateFields($fieldsToMigrate, 'pages_language_overlay');

		/**
		 * Finished category migration
		 */
		$message = 'Title, Canonical and NoIndex are migrated. Run <strong>DB compare</strong> in the install tool to remove the fields from metaseo and run the <strong>DB check</strong> to update the reference index.';
		$status = FlashMessage::OK;
		$title = 'Migrated all fields!';
		$this->messageArray[] = [$status, $title, $message];
	}


	protected function migrateFields($fieldsToMigrate, $table) {
		foreach($fieldsToMigrate as $metaSeoField => $csSeoField) {
			// Settings from page
			$query = 'UPDATE ' . $table . ' SET '.$csSeoField.' = '.$metaSeoField;
			$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}


	/**
	 * Generates output by using flash messages
	 *
	 * @return string
	 */
	protected function generateOutput() {
		$output = '';
		foreach ($this->messageArray as $messageItem) {
			/** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
			$flashMessage = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
				$messageItem[2],
				$messageItem[1],
				$messageItem[0]);
			$output .= $flashMessage->render();
		}
		return $output;
	}

}
