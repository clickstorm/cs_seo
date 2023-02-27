<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Unit\Domain\Model;

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class EvaluationTest extends UnitTestCase
{
    /**
     * @var Evaluation|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            Evaluation::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getResultsReturnsInitialValue(): void
    {
        self::assertSame(
            [],
            $this->subject->getResultsAsArray()
        );
    }

    /**
     * @test
     */
    public function setResultsForStringSetsResults(): void
    {
        $this->subject->setResultsFromArray([1 => 'test']);

        self::assertEquals('a:1:{i:1;s:4:"test";}', $this->subject->_get('results'));
    }

    /**
     * @test
     */
    public function getUrlReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getUrl()
        );
    }

    /**
     * @test
     */
    public function setUrlForStringSetsMyString(): void
    {
        $this->subject->setUrl('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('url'));
    }

    /**
     * @test
     */
    public function getTablenamesReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTablenames()
        );
    }

    /**
     * @test
     */
    public function setTablenamesForStringSetsMyText(): void
    {
        $this->subject->setTablenames('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('tablenames'));
    }

    /**
     * @test
     */
    public function getUidForeignReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getUidForeign()
        );
    }

    /**
     * @test
     */
    public function setUidForeignForIntSetsMyInt(): void
    {
        $this->subject->setUidForeign(12);

        self::assertEquals(12, $this->subject->_get('uidForeign'));
    }

    /**
     * @test
     */
    public function getTstampReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getTstamp()
        );
    }

    /**
     * @test
     */
    public function setTstampForIntSetsMyInt(): void
    {
        $this->subject->setTstamp(12);

        self::assertEquals(12, $this->subject->_get('tstamp'));
    }
}
