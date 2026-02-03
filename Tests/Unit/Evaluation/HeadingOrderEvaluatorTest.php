<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\HeadingOrderEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class HeadingOrderEvaluatorTest extends UnitTestCase
{
    protected ?HeadingOrderEvaluator $generalEvaluationMock = null;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(HeadingOrderEvaluator::class, null, [new \DOMDocument()]);
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock);
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

        self::assertEquals($expectedResult, $result);
    }

    public static function evaluateTestDataProvider(): array
    {
        return [
            'zero headings' => [
                '<html><hr><header>No Headline</header>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_YELLOW,
                    'headings' => [],
                ],
            ],
            'one h1' => [
                '<html><body><h1>h1</h1></body></html>',
                [
                    'count' => 0,
                    'state' => AbstractEvaluator::STATE_GREEN,
                    'headings' => [
                        [
                            'text' => 'h1',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                    ],
                ],
            ],
            'correct order' => [
                '<h1>h1</h1><h1>h1.2</h1><h2>h2</h2><h3>h3</h3><h3>h3.2</h3><h4>h4</h4><h3>h3.3</h3>',
                [
                    'state' => AbstractEvaluator::STATE_GREEN,
                    'count' => 0,
                    'headings' => [
                        [
                            'text' => 'h1',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h1.2',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h2',
                            'level' => 2,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3',
                            'level' => 3,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3.2',
                            'level' => 3,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h4',
                            'level' => 4,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3.3',
                            'level' => 3,
                            'correctOrder' => true,
                        ],
                    ],
                ],
            ],
            'h1 in between' => [
                '<h1>h1</h1><h2>h2</h2><h1>h1</h1><h3>h3</h3>',
                [
                    'state' => AbstractEvaluator::STATE_RED,
                    'count' => 1,
                    'headings' => [
                        [
                            'text' => 'h1',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h2',
                            'level' => 2,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h1',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3',
                            'level' => 3,
                            'correctOrder' => false,
                        ],
                    ],
                ],
            ],
            'begin with h2' => [
                '<h2>h2</h2><h1>h1</h1><h3>h3</h3>',
                [
                    'state' => AbstractEvaluator::STATE_RED,
                    'count' => 2,
                    'headings' => [
                        [
                            'text' => 'h2',
                            'level' => 2,
                            'correctOrder' => false,
                        ],
                        [
                            'text' => 'h1',
                            'level' => 1,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3',
                            'level' => 3,
                            'correctOrder' => false,
                        ],
                    ],
                ],
            ],
            'begin with h3' => [
                '<h3>h3</h3><h4>h4</h4><h4>h4.2</h4><h3>h3.2</h3><h6>h6</h6>',
                [
                    'state' => AbstractEvaluator::STATE_RED,
                    'count' => 2,
                    'headings' => [
                        [
                            'text' => 'h3',
                            'level' => 3,
                            'correctOrder' => false,
                        ],
                        [
                            'text' => 'h4',
                            'level' => 4,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h4.2',
                            'level' => 4,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h3.2',
                            'level' => 3,
                            'correctOrder' => true,
                        ],
                        [
                            'text' => 'h6',
                            'level' => 6,
                            'correctOrder' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
