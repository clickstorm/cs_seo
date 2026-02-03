<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Upgrades\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

#[UpgradeWizard('csseo_metaDataImagesUpgradeWizard')]
final readonly class MetaDataImagesUpgradeWizard implements UpgradeWizardInterface
{
    private const TABLE_SYS_FILE_REFERENCE = 'sys_file_reference';
    private const TABLE_META_DATA = 'tx_csseo_domain_model_meta';
    private const FIELD_TABLENAMES = 'tablenames';
    private const FIELD_FIELDNAME = 'fieldname';
    private const FIELD_UID = 'uid';
    private const OLD_FIELD_PREFIX = 'tx_csseo_';
    private const OLD_FIELD_NAME_TO_NEW_MAP = [
        'tx_csseo_og_image' => 'og_image',
        'tx_csseo_tw_image' => 'tw_image',
    ];
    public function __construct(private ConnectionPool $connectionPool) {}

    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return 'EXT:cs_seo - fix relations for MetaData images';
    }

    /**
     * Return the description for this wizard
     */
    public function getDescription(): string
    {
        return <<<'EOD'
            Replaces in sys_file_reference the fieldnames for tx_csseo_domain_model_meta, e.g. tx_csseo_og_image becomes og_image.
            Otherwise the images will not be found.
            EOD;
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     */
    public function executeUpdate(): bool
    {
        foreach (self::OLD_FIELD_NAME_TO_NEW_MAP as $oldFieldValue => $newFieldValue) {
            $query = $this->getQueryBuilderForSysFileReference();
            $query
                ->update(self::TABLE_SYS_FILE_REFERENCE)
                ->set(
                    self::FIELD_FIELDNAME,
                    $newFieldValue
                )
                ->where($query->expr()->eq(self::FIELD_TABLENAMES, $query->createNamedParameter(self::TABLE_META_DATA)))
                ->andWhere($query->expr()->eq(self::FIELD_FIELDNAME, $query->createNamedParameter($oldFieldValue)))
                ->executeStatement();
        }

        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        $query = $this->getQueryBuilderForSysFileReference();
        return (bool)$query
            ->count(self::FIELD_UID)
            ->from(self::TABLE_SYS_FILE_REFERENCE)
            ->where($query->expr()->eq(self::FIELD_TABLENAMES, $query->createNamedParameter(self::TABLE_META_DATA)))
            ->andWhere($query->expr()->like(self::FIELD_FIELDNAME, $query->createNamedParameter(self::OLD_FIELD_PREFIX . '%')))
            ->executeQuery()->fetchOne();
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function getQueryBuilderForSysFileReference(): QueryBuilder
    {
        $query = $this->connectionPool
            ->getQueryBuilderForTable(self::TABLE_SYS_FILE_REFERENCE);
        $query->getRestrictions()->removeAll();
        return $query;
    }
}
