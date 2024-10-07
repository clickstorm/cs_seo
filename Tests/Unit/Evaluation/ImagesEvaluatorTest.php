<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\ImagesEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ImagesEvaluatorTest extends UnitTestCase
{
    protected ?ImagesEvaluator $generalEvaluationMock = null;

    public function setUp(): void
    {
        $this->generalEvaluationMock = $this->getAccessibleMock(
            ImagesEvaluator::class,
            null,
            [new \DOMDocument()]
        );
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

        self::assertEquals(json_encode($expectedResult, JSON_THROW_ON_ERROR), json_encode($result, JSON_THROW_ON_ERROR));
    }

    public static function evaluateTestDataProvider(): array
    {
        return [
            'zero images' => [
                '<html>',
                [
                    'count' => 0,
                    'altCount' => 0,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'one image no alt' => [
                '<img alt="" />',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => [''],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'one image with alt' => [
                '<img alt="Hallo" />',
                [
                    'count' => 1,
                    'altCount' => 1,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'one alt missing' => [
                '<img alt="" src="myImage.png" /><img alt="Test" />',
                [
                    'count' => 2,
                    'altCount' => 1,
                    'countWithoutAlt' => 1,
                    'images' => ['myImage.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            '3 images with alt' => [
                str_repeat('<img alt="Test" />', 3),
                [
                    'count' => 3,
                    'altCount' => 3,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
        ];
    }
}
