<?php

namespace Clickstorm\CsSeo\Service;

use TYPO3\CMS\Core\Context\LanguageAspect;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class MetaDataService
{
    public const TABLE_NAME_META = 'tx_csseo_domain_model_meta';

    protected ?ContentObjectRenderer $cObj = null;

    protected ?PageRepository $pageRepository = null;

    protected ?LanguageAspect $languageAspect = null;

    public function __construct()
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
            $tableSettings = $this->getCurrentTableConfiguration($tables, $this->cObj);

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

                // db fallback
                if (isset($tableSettings['fallback'])) {
                    foreach ($tableSettings['fallback'] as $seoField => $fallbackField) {
                        if (empty($metaData[$seoField])) {
                            if (!empty($record[$fallbackField])) {
                                $metaData[$seoField] = $record[$fallbackField];
                                if ($seoField === 'og_image' || $seoField === 'tw_image') {
                                    $metaData[$seoField] = [
                                        'field' => $fallbackField,
                                        'table' => $tableSettings['table'],
                                        'uid_foreign' => $tableSettings['uid'],
                                    ];
                                }
                            } // check for double slash, if so multiple fallback fields are defined, the first with content will be used
                            elseif (strpos($fallbackField, '//') !== false) {
                                foreach (GeneralUtility::trimExplode('//', $fallbackField) as $possibleFallbackField) {
                                    if (!empty($record[$possibleFallbackField])) {
                                        $metaData[$seoField] = $record[$possibleFallbackField];
                                    }
                                }
                            } // check for curly brackets, if so, replace the brackets with their corresponding metaData $seoField
                            elseif (preg_match('/{([^}]+)}/', $fallbackField)) {
                                $curlyBracketSeoField = $fallbackField;
                                $matches = [];
                                preg_match_all('/{([^}]+)}/', $fallbackField, $matches);
                                $matchesWithCurlyBrackets = $matches[0];
                                $matchesWithoutCurlyBrackets = $matches[1];
                                foreach ($matchesWithCurlyBrackets as $key => $matchWithCurlyBracket) {
                                    $recordField = $matchesWithoutCurlyBrackets[$key];
                                    $curlyBracketSeoField = str_replace(
                                        $matchWithCurlyBracket,
                                        $record[$recordField],
                                        $curlyBracketSeoField
                                    );
                                }
                                $metaData[$seoField] = $curlyBracketSeoField;
                            }
                        }
                    }
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableSettings['table']);

        $row = $queryBuilder->select('*')
            ->from($tableSettings['table'])->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME_META);

        $res = $queryBuilder->select('*')
            ->from(self::TABLE_NAME_META)->where($queryBuilder->expr()->eq(
            'uid_foreign',
            $queryBuilder->createNamedParameter($tableSettings['uid'], \PDO::PARAM_INT)
        ), $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableSettings['table'])))->executeQuery()->fetchAllAssociative();

        return isset($res[0]) ? $res[0] : [];
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
