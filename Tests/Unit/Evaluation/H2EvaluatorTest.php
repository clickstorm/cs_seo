<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\H2Evaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class H2EvaluatorTest extends UnitTestCase
{
    protected ?H2Evaluator $generalEvaluationMock = null;

    protected int $max = 6;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(H2Evaluator::class, ['dummy'], [new \DOMDocument()]);
        $extConf = [
            'maxH2' => $this->max,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock, $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo']);
    }

    /**
     * htmlspecialcharsOnArray Test
     *
     * @dataProvider evaluateTestDataProvider
     * @test
     * @throws \JsonException
     */
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

    /**
     * Dataprovider evaluateTest()
     *
     * @return array
     */
    public function evaluateTestDataProvider()
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
                str_repeat('<h2>Headline</h2>', $this->max),
                [
                    'state' => AbstractEvaluator::STATE_GREEN,
                    'count' => $this->max,
                ],
            ],
            'seven h2' => [
                str_repeat('<h2>Headline</h2>', $this->max + 1),
                [
                    'state' => AbstractEvaluator::STATE_YELLOW,
                    'count' => $this->max + 1,
                ],
            ],
        ];
    }
}
