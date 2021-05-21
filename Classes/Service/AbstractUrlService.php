<?php

namespace Clickstorm\CsSeo\Service;

use Clickstorm\CsSeo\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

abstract class AbstractUrlService
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * constructor
     *
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function __construct(
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        if ($typoScriptFrontendController === null) {
            $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        }
        $this->typoScriptFrontendController = $typoScriptFrontendController;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return int
     */
    protected function getLanguageFromItem($table, $uid)
    {
        if ($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $items = $queryBuilder->select($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetchAll();

            return $items[0]['sys_language_uid'];
        }

        return 0;
    }

    /**
     * @param string $table
     * @param string $uid
     *
     * @return array
     */
    protected function getAllLanguagesFromItem($table, $uid)
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
        if($languageUidOfCurrentRecord > 0 && $l10nParentOfCurrentRecord) {
            $uid = $l10nParentOfCurrentRecord;
        }

        $allItems = $queryBuilder->select($languageField)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    $pointerField,
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->orWhere(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        foreach ($allItems as $item) {
            $languageIds[] = $item[$languageField];
        }

        return $languageIds;
    }

    /**
     * @param string $uid
     *
     * @return int
     */
    protected function getCanonicalFromAllLanguagesOfPage($uid)
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
            )
            ->orWhere(
                $queryBuilder->expr()->eq(
                    $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'],
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        foreach ($items as $item) {
            $res[$item['sys_language_uid']] = $item['canonical_link'];
        }

        return $res;
    }
}
