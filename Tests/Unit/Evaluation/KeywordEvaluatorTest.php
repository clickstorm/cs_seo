<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\KeywordEvaluator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class KeywordEvaluatorTest extends UnitTestCase
{
    /**
     * @var KeywordEvaluator
     */
    protected $generalEvaluationMock;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(
            KeywordEvaluator::class,
            ['dummy'],
            [new \DOMDocument()]
        );
    }

    public function tearDown(): void
    {
        unset($this->generalEvaluationMock);
    }

    /**
     * htmlspecialcharsOnArray Test
     *
     * @param string $html
     * @param mixed $expectedResult
     *
     * @dataProvider evaluateTestDataProvider
     * @test
     */
    public function evaluateTest($html, $keyword, $expectedResult)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $this->generalEvaluationMock->setDomDocument($domDocument);
        $this->generalEvaluationMock->setKeyword($keyword);
        $result = $this->generalEvaluationMock->evaluate();

        ksort($expectedResult);
        ksort($result);

        self::assertEquals(json_encode($expectedResult), json_encode($result));
    }

    /**
     * Dataprovider evaluateTest()
     *
     * @return array
     */
    public function evaluateTestDataProvider()
    {
        return [
            'no keyword' => [
                '<html>',
                '',
                [
                    'notSet' => 1,
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'keyword equals , only' => [
                '<title>Test</title>',
                ',',
                [
                    'contains' => [
                        'title' => 0,
                        'description' => 0,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword set, not found' => [
                '<html>',
                'Test',
                [
                    'contains' => [
                        'title' => 0,
                        'description' => 0,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword different case, found in title' => [
                '<title>Test</title>',
                'test',
                [
                    'contains' => [
                        'title' => 1,
                        'description' => 0,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword set, found in title' => [
                '<title>Test</title>',
                'Test',
                [
                    'contains' => [
                        'title' => 1,
                        'description' => 0,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword set, found in description' => [
                '<meta name="description" content="Test">',
                'Test',
                [
                    'contains' => [
                        'title' => 0,
                        'description' => 1,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword set, found in body' => [
                '<body>Test</body>',
                'Test',
                [
                    'contains' => [
                        'title' => 0,
                        'description' => 0,
                        'body' => 1,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword set, found everywhere UTF-8' => [
                '<head><title>ÜÄöß</title><meta name="description" content="Test ÜÄöß "></head><body>ÜÄöß Test</body>',
                'ÜÄöß',
                [
                    'contains' => [
                        'title' => 1,
                        'description' => 1,
                        'body' => 1,
                    ],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'keyword set, found everywhere' => [
                '<head><title>Test Test</title><meta name="description" content="Test this Test"></head><body>Here Test</body>',
                'Test',
                [
                    'contains' => [
                        'title' => 2,
                        'description' => 2,
                        'body' => 1,
                    ],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'keyword with , and empty string' => [
                '<title>Test</title>',
                'Test,',
                [
                    'contains' => [
                        'title' => 1,
                        'description' => 0,
                        'body' => 0,
                    ],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'keyword alternative set, found everywhere' => [
                '<head>
						<title>Test TYPO3</title>
						<meta name="description" content="Test in TYPO3 like Test TYPO3">
				</head>
				<body>
					Sed augue ipsum, egestas nec, vestibulum et, malesuada adipiscing, dui. Praesent venenatis metus at
					tortor pulvinar varius. Test TYPO3 Vestibulum volutpat pretium libero. Pellentesque posuere.
					Quisque id odio. Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam.
					Pellentesque egestas, neque sit amet convallis pulvinar, justo nulla eleifend augue, ac auctor
					orci leo non est. Tests TYPO3 Phasellus tempus.
				</body>',
                'Test TYPO3, Test in TYPO3, Tests TYPO3',
                [
                    'contains' => [
                        'title' => 1,
                        'description' => 2,
                        'body' => 2,
                    ],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
        ];
    }
}
