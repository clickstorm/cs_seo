<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class H2EvaluatorTest extends UnitTestCase
{
    protected ?H2Evaluator $generalEvaluationMock = null;

    protected static int $max = 6;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(H2Evaluator::class, null, [new \DOMDocument()]);
        $extConf = [
            'maxH2' => self::$max,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock, $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']);
    }

    #[DataProvider('evaluateTestDataProvider')]
    #[Test]
    public function evaluateTest(string $html, array $expectedResult): void
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        self::assertEquals(json_encode($expectedResult, JSON_THROW_ON_ERROR), json_encode($result, JSON_THROW_ON_ERROR));
    }

    public static function evaluateTestDataProvider(): array
    {
        return [
            'zero h2' => [
                '<html>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'one h2' => [
                '<html><body><h2>Headline One</h2></body></html>',
                [
                    'count' => 1,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'two h2' => [
                '<h2>Headline One</h2><h2>Headline Two</h2>',
                [
                    'state' => AbstractEvaluator::STATE_GREEN,
                    'count' => 2,
                ],
            ],
            'six h2' => [
                str_repeat('<h2>Headline</h2>', self::$max),
                [
                    'state' => AbstractEvaluator::STATE_GREEN,
                    'count' => self::$max,
                ],
            ],
            'seven h2' => [
                str_repeat('<h2>Headline</h2>', self::$max + 1),
                [
                    'state' => AbstractEvaluator::STATE_YELLOW,
                    'count' => self::$max + 1,
                ],
            ],
        ];
    }
}
