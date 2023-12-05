<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\H1Evaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class H1EvaluatorTest extends UnitTestCase
{
    protected ?H1Evaluator $generalEvaluationMock = null;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(H1Evaluator::class, ['dummy'], [new \DOMDocument()]);
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock);
    }

    /**
     * @dataProvider evaluateTestDataProvider
     * @test
     */
    public function evaluateTest(string $html, array $expectedResult): void
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        self::assertEquals($expectedResult, $result);
    }

    /**
     * Dataprovider evaluateTest()
     */
    public function evaluateTestDataProvider(): array
    {
        return [
            'zero h1' => [
                '<html>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'one h1' => [
                '<html><body><h1>Headline One</h1></body></html>',
                [
                    'count' => 1,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'two h1' => [
                '<h1>Headline One</h1><h1>Headline Two</h1>',
                [
                    'state' => AbstractEvaluator::STATE_RED,
                    'count' => 2,
                ],
            ],
        ];
    }
}
