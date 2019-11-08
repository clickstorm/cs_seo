<?php

namespace Clickstorm\CsSeo\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes
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

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Class EvaluationRepository
 *
 */
class EvaluationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    protected $respectStoragePage = false;

    public function initializeObject()
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $defaultQuerySettings->setRespectStoragePage(false);
        $defaultQuerySettings->setRespectSysLanguage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uidForeign
     * @param string $tableName
     *
     * @return object The matching object if found, otherwise NULL
     */
    public function findByUidForeignAndTableName($uidForeign, $tableName)
    {
        $query = $this->createQuery();

        $constraints = [];

        $constraints[] = $query->equals('uid_foreign', $uidForeign);
        $constraints[] = $query->equals('tablenames', $tableName);

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }
}
