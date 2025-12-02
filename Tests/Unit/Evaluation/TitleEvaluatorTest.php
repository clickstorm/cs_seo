<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TitleEvaluatorTest extends UnitTestCase
{
    protected ?TitleEvaluator $generalEvaluationMock = null;

    protected static int $min = 40;

    protected static int $max = 57;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(
            TitleEvaluator::class,
            null,
            [new \DOMDocument()]
        );
        $extConf = [
            'minTitle' => self::$min,
            'maxTitle' => self::$max,
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
    public function evaluateTest(string $html, mixed $expectedResult): void
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        self::assertEquals(json_encode($expectedResult), json_encode($result));
    }

    public static function evaluateTestDataProvider(): array
    {
        return [
            'zero title' => [
                '<html>',
                [
                    'count' => 0,
                    'state' => TitleEvaluator::STATE_RED,
                ],
            ],
            'count special chars' => [
                '<title>ÄÖÜß</title>',
                [
                    'count' => 4,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ],
            ],
            'short title' => [
                '<title>' . str_repeat('.', self::$min - 1) . '</title>',
                [
                    'count' => self::$min - 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ],
            ],
            'min good title' => [
                '<title>' . str_repeat('.', self::$min) . '</title>',
                [
                    'count' => self::$min,
                    'state' => TitleEvaluator::STATE_GREEN,
                ],
            ],
            'max good title' => [
                '<title>' . str_repeat('.', self::$max) . '</title>',
                [
                    'count' => self::$max,
                    'state' => TitleEvaluator::STATE_GREEN,
                ],
            ],
            'long title' => [
                '<title>' . str_repeat('.', self::$max + 1) . '</title>',
                [
                    'count' => self::$max + 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ],
            ],
        ];
    }
}
