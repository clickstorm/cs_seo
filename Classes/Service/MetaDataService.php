<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Context\LanguageAspect;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use Clickstorm\CsSeo\Service\FallbackResolverService;

class MetaDataService
{
    public const TABLE_NAME_META = 'tx_csseo_domain_model_meta';

    protected ?ContentObjectRenderer $cObj = null;

    protected ?PageRepository $pageRepository = null;

    protected ?LanguageAspect $languageAspect = null;

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $context = clone GeneralUtility::makeInstance(Context::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, $context);
        $this->languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
    }

    public function getMetaData(): array|bool
    {
        // check if metadata was already set
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'])) {
            return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'];
        }

        // get table settings
        $tables = ConfigurationUtility::getTablesToExtend();

        if ($tables !== []) {
            // get active table name und settings
            $tableSettings = static::getCurrentTableConfiguration($tables, $this->cObj);

            if ($tableSettings) {
                // get record
                $record = $this->getRecord($tableSettings);
                if (!is_array($record)) {
                    return false;
                }

                if (!empty($record['_LOCALIZED_UID'])) {
                    $tableSettings['uid'] = $record['_LOCALIZED_UID'];
                }
                // db meta
                $metaData = $this->getMetaProperties($tableSettings);
                $metaData['__uid'] = $tableSettings['uid'];

                // db fallback (moved to service)
                if (isset($tableSettings['fallback'])) {
                    $resolver = GeneralUtility::makeInstance(FallbackResolverService::class);
                    $metaData = $resolver->applyFallbacks($metaData, $tableSettings['fallback'], $record, $tableSettings);
                }

                // set metaData in Globals
                $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'] = $metaData;

                return $metaData;
            }
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']['storage']['metaData'] = false;

        return false;
    }

    /**
     * Check if extension detail view or page properties should be used
     */
    public static function getCurrentTableConfiguration(array $tables, ContentObjectRenderer $cObj, bool $checkOnly = false): array|bool
    {
        foreach ($tables as $tableName => $tableSettings) {
            if (isset($tableSettings['enable'])) {
                $uid = (int)($cObj->getData($tableSettings['enable']));

                if ($uid !== 0) {
                    if ($checkOnly) {
                        return true;
                    }
                    $data = [
                        'table' => $tableName,
                        'uid' => $uid,
                    ];

                    if (isset($tableSettings['fallback']) && count($tableSettings['fallback']) > 0) {
                        $data['fallback'] = $tableSettings['fallback'];
                    }

                    return $data;
                }
            }
        }

        return false;
    }

    /**
     * DB query to get the fallback properties
     */
    protected function getRecord(array $tableSettings): ?array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableSettings['table']);

        $row = $queryBuilder->select('*')
            ->from($tableSettings['table'])->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($tableSettings['uid'], Connection::PARAM_INT)
            ))->executeQuery()
            ->fetchAssociative();

        if (is_array($row)) {
            $this->pageRepository->versionOL($tableSettings['table'], $row);
            $row = $this->pageRepository->getLanguageOverlay(
                $tableSettings['table'],
                $row,
                $this->languageAspect
            );
        } else {
            return null;
        }

        return $row;
    }

    /**
     * DB query to get the current meta properties
     */
    protected function getMetaProperties(array $tableSettings): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME_META);

        $res = $queryBuilder->select('*')
            ->from(self::TABLE_NAME_META)->where(
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($tableSettings['uid'], Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableSettings['table'])
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return $res[0] ?? [];
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
