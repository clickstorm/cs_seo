<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

abstract class AbstractUrlService
{
    protected ?TypoScriptFrontendController $typoScriptFrontendController = null;

    /**
     * languages defined in site config
     */
    protected array $siteLanguages = [];

    /**
     * constructor
     */
    public function __construct(
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        if ($typoScriptFrontendController === null) {
            $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        }
        $this->typoScriptFrontendController = $typoScriptFrontendController;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function getLanguageFromItem(string $table, int $uid): int
    {
        if ($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                ->from($table)->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
            ))->executeQuery()
                ->fetchAll();

            return $items[0]['sys_language_uid'];
        }

        return 0;
    }

    /**
     * @throws Exception
     */
    protected function getAllLanguagesFromItem(string $table, int $uid): array
    {
        $languageIds = [];
        if (!isset($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']) || !isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])) {
            return $languageIds;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $pointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        // first check if sys_language_uid of record is 0
        $select = implode(',', [$pointerField, $languageField, 'uid']);
        $currentRecord = DatabaseUtility::getRecord($table, $uid, $select);
        $languageUidOfCurrentRecord = (int)$currentRecord[$languageField];
        $l10nParentOfCurrentRecord = (int)$currentRecord[$pointerField];

        // if languageUid of current record is not default and l10nParen is set, use the uid of the default language record
        if ($languageUidOfCurrentRecord > 0 && $l10nParentOfCurrentRecord) {
            $uid = $l10nParentOfCurrentRecord;
        }

        // first get all items
        $allItems = $queryBuilder->select($languageField)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    $pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )->orWhere($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
        ))->executeQuery()
            ->fetchAll();

        // second get all items with canonical or no_index, to remove them
        // until https://forge.typo3.org/issues/86385 is not fixed, this has to be done in two queries

        // first get all items
        $invalidItemsRes = $queryBuilder->select('t.' . $languageField, 't.uid')
            ->from($table, 't')
            ->leftJoin(
                't',
                'tx_csseo_domain_model_meta',
                'm'
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'm.uid_foreign',
                    $queryBuilder->quoteIdentifier('t.uid')
                ),
                $queryBuilder->expr()->eq(
                    'm.tablenames',
                    $queryBuilder->createNamedParameter($table)
                ),
                $queryBuilder->expr()->or($queryBuilder->expr()->eq('m.no_index', 1), $queryBuilder->expr()->neq(
                    'm.canonical',
                    $queryBuilder->createNamedParameter('')
                )),
                $queryBuilder->expr()->or($queryBuilder->expr()->eq(
                    't.' . $pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ), $queryBuilder->expr()->eq(
                    't.uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ))
            )->executeQuery()
            ->fetchAllAssociative();

        $invalidItems = [];
        foreach ($invalidItemsRes as $item) {
            $invalidItems[$item[$languageField]] = $item['uid'];
        }

        foreach ($allItems as $item) {
            if (!isset($invalidItems[$item[$languageField]])) {
                $languageIds[$item[$languageField]] = $item[$languageField];
            }
        }

        // if not already defined, get the site languages
        if (empty($this->siteLanguages) && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {
            $this->siteLanguages = $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getLanguages();
        }

        /** @var SiteLanguage $siteLanguage */
        foreach ($this->siteLanguages as $siteLanguage) {
            // @extensionScannerIgnoreLine
            if (isset($languageIds[$siteLanguage->getLanguageId()])) {
                continue;
            }

            if ($siteLanguage instanceof SiteLanguage && $siteLanguage->getFallbackType() === 'fallback' && $siteLanguage->getFallbackLanguageIds()) {
                foreach ($siteLanguage->getFallbackLanguageIds() as $fallbackLanguageId) {
                    if (isset($languageIds[$fallbackLanguageId])) {
                        // @extensionScannerIgnoreLine
                        $languageIds[$siteLanguage->getLanguageId()] = $fallbackLanguageId;
                    }
                }
            }
        }

        return $languageIds;
    }

    protected function getCanonicalFromAllLanguagesOfPage(int $uid): array
    {
        $res = [];

        $table = 'pages';
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'], 'canonical_link')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )->orWhere($queryBuilder->expr()->eq(
            $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'],
            $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
        ))->executeQuery()
            ->fetchAll();

        foreach ($items as $item) {
            $res[$item['sys_language_uid']] = $item['canonical_link'];
        }

        return $res;
    }
}
