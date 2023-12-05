<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DescriptionEvaluatorTest extends UnitTestCase
{
    protected ?DescriptionEvaluator $generalEvaluationMock = null;

    protected int $min = 140;

    protected int $max = 160;

    public function setUp(): void
    {
        $this->generalEvaluationMock =
            $this->getAccessibleMock(DescriptionEvaluator::class, ['dummy'], [new \DOMDocument()]);
        $extConf = [
            'minDescription' => $this->min,
            'maxDescription' => $this->max,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = '';
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock, $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils']);
    }

    /**
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
     */
    public function evaluateTestDataProvider(): array
    {
        return [
            'zero description' => [
                '<html>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'short decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->min - 1) . '" />',
                [
                    'count' => $this->min - 1,
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'min good decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->min) . '" />',
                [
                    'count' => $this->min,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'max good decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->max) . '" />',
                [
                    'count' => $this->max,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'long decription' => [
                '<meta name="description" content="' . str_repeat('.', $this->max + 1) . '" />',
                [
                    'count' => $this->max + 1,
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
        ];
    }
}
