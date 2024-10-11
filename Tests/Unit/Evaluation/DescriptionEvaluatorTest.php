<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\DescriptionEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DescriptionEvaluatorTest extends UnitTestCase
{
    protected ?DescriptionEvaluator $generalEvaluationMock = null;

    protected static int $min = 140;

    protected static int $max = 160;

    public function setUp(): void
    {
        $this->generalEvaluationMock =
            $this->getAccessibleMock(DescriptionEvaluator::class, null, [new \DOMDocument()]);
        $extConf = [
            'minDescription' => self::$min,
            'maxDescription' => self::$max,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = '';
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock, $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils']);
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
            'zero description' => [
                '<html>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'short decription' => [
                '<meta name="description" content="' . str_repeat('.', self::$min - 1) . '" />',
                [
                    'count' => self::$min - 1,
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'min good decription' => [
                '<meta name="description" content="' . str_repeat('.', self::$min) . '" />',
                [
                    'count' => self::$min,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'max good decription' => [
                '<meta name="description" content="' . str_repeat('.', self::$max) . '" />',
                [
                    'count' => self::$max,
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'long decription' => [
                '<meta name="description" content="' . str_repeat('.', self::$max + 1) . '" />',
                [
                    'count' => self::$max + 1,
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
        ];
    }
}
