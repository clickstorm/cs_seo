<?php

namespace Clickstorm\CsSeo\Tests\Updates;

use Clickstorm\CsSeo\Updates\PagesUpdater;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PagesUpdaterTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = ['seo'];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/Extensions/cs_seo_3'
    ];

    /** @var  \Clickstorm\CsSeo\Updates\PagesUpdater */
    protected $pagesUpdater;

    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');

        $this->pagesUpdater = new PagesUpdater();
    }

    /**
     * @test
     */
    public function executeUpdate()
    {
        $this->pagesUpdater->executeUpdate();

        $exp = [
            'seo_title' => 'old title',
            'no_index' => 1
        ];

        $actual = $this->getRow(1, array_keys($exp));

        $this->assertEquals($exp, $actual);
    }

    protected function getRow($id, $fields)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $queryBuilder
            ->select(...$fields)
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT))
            )
            ->setMaxResults(1);

        return $queryBuilder->execute()->fetch();
    }
}