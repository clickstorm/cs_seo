<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\TitleEvaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TitleEvaluatorTest extends UnitTestCase
{
    protected ?TitleEvaluator $generalEvaluationMock = null;

    protected int $min = 40;

    protected int $max = 57;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(TitleEvaluator::class, ['dummy'], [new \DOMDocument()]);
        $extConf = [
            'minTitle' => $this->min,
            'maxTitle' => $this->max,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'] = $extConf;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'] = '';
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock, $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['cs_seo'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils']);
    }

    /**
     * htmlspecialcharsOnArray Test
     *
     * @dataProvider evaluateTestDataProvider
     * @test
     */
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

    /**
     * Dataprovider evaluateTest()
     */
    public function evaluateTestDataProvider(): array
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
                '<title>' . str_repeat('.', $this->min - 1) . '</title>',
                [
                    'count' => $this->min - 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ],
            ],
            'min good title' => [
                '<title>' . str_repeat('.', $this->min) . '</title>',
                [
                    'count' => $this->min,
                    'state' => TitleEvaluator::STATE_GREEN,
                ],
            ],
            'max good title' => [
                '<title>' . str_repeat('.', $this->max) . '</title>',
                [
                    'count' => $this->max,
                    'state' => TitleEvaluator::STATE_GREEN,
                ],
            ],
            'long title' => [
                '<title>' . str_repeat('.', $this->max + 1) . '</title>',
                [
                    'count' => $this->max + 1,
                    'state' => TitleEvaluator::STATE_YELLOW,
                ],
            ],
        ];
    }
}
