<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Unit\Domain\Model;

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
    protected mixed $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            Evaluation::class,
            ['getResults']
        );
    }

    #[Test]
    public function getResultsReturnsInitialValue(): void
    {
        self::assertSame(
            [],
            $this->subject->getResultsAsArray()
        );
    }

    #[Test]
    public function setResultsForStringSetsResults(): void
    {
        $this->subject->setResultsFromArray([1 => 'test']);

        self::assertEquals('a:1:{i:1;s:4:"test";}', $this->subject->_get('results'));
    }

    #[Test]
    public function getUrlReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getUrl()
        );
    }

    #[Test]
    public function setUrlForStringSetsMyString(): void
    {
        $this->subject->setUrl('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('url'));
    }

    #[Test]
    public function getTablenamesReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTablenames()
        );
    }

    #[Test]
    public function setTablenamesForStringSetsMyText(): void
    {
        $this->subject->setTablenames('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('tablenames'));
    }

    #[Test]
    public function getUidForeignReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getUidForeign()
        );
    }

    #[Test]
    public function setUidForeignForIntSetsMyInt(): void
    {
        $this->subject->setUidForeign(12);

        self::assertEquals(12, $this->subject->_get('uidForeign'));
    }

    #[Test]
    public function getTstampReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getTstamp()
        );
    }

    #[Test]
    public function setTstampForIntSetsMyInt(): void
    {
        $this->subject->setTstamp(12);

        self::assertEquals(12, $this->subject->_get('tstamp'));
    }
}
