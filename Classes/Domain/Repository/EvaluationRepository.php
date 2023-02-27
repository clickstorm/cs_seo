<?php

namespace Clickstorm\CsSeo\Domain\Repository;

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EvaluationRepository extends Repository
{
    protected $respectStoragePage = false;

    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $defaultQuerySettings->setRespectStoragePage(false);
        $defaultQuerySettings->setRespectSysLanguage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    public function findByUidForeignAndTableName(int $uidForeign, string $tableName): ?Evaluation
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('uid_foreign', $uidForeign),
                $query->equals('tablenames', $tableName)
            )
        );

        return $query->execute()->getFirst();
    }
}
