<?php

namespace Clickstorm\CsSeo\Tests\Unit\Evaluation;

use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;
use Clickstorm\CsSeo\Evaluation\ImagesEvaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ImagesEvaluatorTest extends UnitTestCase
{
    protected ?ImagesEvaluator $subject = null;

    public function setUp(): void
    {
        $this->subject = $this->getAccessibleMock(
            ImagesEvaluator::class,
            null,
            [new \DOMDocument()]
        );
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    #[DataProvider('evaluateTestDataProvider')]
    #[Test]
    public function evaluateTest(string $html, array $expectedResult): void
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->subject->setDomDocument($domDocument);

        $result = $this->subject->evaluate();

        $expectedResult += [
            'additionalImagesCount' => 0,
            'omittedOversizedEmbeddedImagesCount' => 0,
        ];

        ksort($expectedResult);
        ksort($result);

        self::assertSame($expectedResult, $result);
    }

    #[DataProvider('extractImageUrlTestDataProvider')]
    #[Test]
    public function extractImageUrlTest(string $html, string $expectedUrl): void
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $image = $domDocument->getElementsByTagName('img')->item(0);
        self::assertInstanceOf(\DOMElement::class, $image);

        $method = new \ReflectionMethod($this->subject, 'extractImageUrl');
        $actualUrl = $method->invoke($this->subject, $image);

        self::assertSame($expectedUrl, $actualUrl);
    }

    #[Test]
    public function evaluateOmitsOversizedEmbeddedImageUrls(): void
    {
        $embeddedPayload = str_repeat('A', 1100000);
        $html = '<img alt="" src="data:image/svg+xml;base64,' . $embeddedPayload . '" />';

        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->subject->setDomDocument($domDocument);

        $result = $this->subject->evaluate();

        self::assertSame(1, $result['countWithoutAlt']);
        self::assertSame([], $result['images']);
        self::assertSame(1, $result['additionalImagesCount']);
        self::assertSame(1, $result['omittedOversizedEmbeddedImagesCount']);
    }

    #[Test]
    public function evaluateKeepsSmallEmbeddedImageUrls(): void
    {
        $embeddedPayload = str_repeat('A', 200);
        $html = '<img alt="" src="data:image/svg+xml;base64,' . $embeddedPayload . '" />';

        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);
        $this->subject->setDomDocument($domDocument);

        $result = $this->subject->evaluate();

        self::assertSame(1, $result['countWithoutAlt']);
        self::assertCount(1, $result['images']);
        self::assertStringStartsWith('data:image/svg+xml;base64,', $result['images'][0]);
        self::assertSame(0, $result['additionalImagesCount']);
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
            'one image without alt text' => [
                '<img alt="" />',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => [''],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'one image with alt text' => [
                '<img alt="Hello" />',
                [
                    'count' => 1,
                    'altCount' => 1,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'alt text containing zero is not empty' => [
                '<img alt="0" src="image.png" />',
                [
                    'count' => 1,
                    'altCount' => 1,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'one alt text missing' => [
                '<img alt="" src="myImage.png" /><img alt="Test" />',
                [
                    'count' => 2,
                    'altCount' => 1,
                    'countWithoutAlt' => 1,
                    'images' => ['myImage.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'three images with alt text' => [
                str_repeat('<img alt="Test" />', 3),
                [
                    'count' => 3,
                    'altCount' => 3,
                    'countWithoutAlt' => 0,
                    'images' => [],
                    'state' => AbstractEvaluator::STATE_GREEN,
                ],
            ],
            'decorative image with presentation role' => [
                '<img alt="" src="myImage.png" role="presentation" /><img alt="Test" /><img alt="" src="foo.png"/>',
                [
                    'count' => 3,
                    'altCount' => 2,
                    'countWithoutAlt' => 1,
                    'images' => ['foo.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'decorative image with none role' => [
                '<img alt="" src="myImage.png" role="NONE" /><img alt="" src="foo.png"/>',
                [
                    'count' => 2,
                    'altCount' => 1,
                    'countWithoutAlt' => 1,
                    'images' => ['foo.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'decorative image with aria hidden' => [
                '<img alt="" src="myImage.png" aria-hidden="TRUE" /><img alt="Test" /><img alt="" src="foo.png"/>',
                [
                    'count' => 3,
                    'altCount' => 2,
                    'countWithoutAlt' => 1,
                    'images' => ['foo.png'],
                    'state' => AbstractEvaluator::STATE_YELLOW,
                ],
            ],
            'prefer img srcset over src' => [
                '<img alt="" src="fallback.png" srcset="chosen-small.jpg 320w, chosen-large.jpg 1280w" />',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['chosen-small.jpg'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'use picture source srcset before img srcset' => [
                '<picture><source srcset="from-source.jpg 1x, from-source@2x.jpg 2x"><img alt="" src="fallback.png" srcset="from-img.jpg 1x"></picture>',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['from-source.jpg'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'resolve root relative URL against base URL' => [
                '<base href="https://example.test/sub/page/"><img alt="" src="/fileadmin/image.jpg">',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['https://example.test/fileadmin/image.jpg'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'resolve parent relative URL against base URL' => [
                '<base href="https://example.test/sub/page/"><img alt="" src="../image.jpg">',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['https://example.test/sub/image.jpg'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'use first base element with href' => [
                '<base href="https://first.example/path/"><base href="https://second.example/"><img alt="" src="image.jpg">',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['https://first.example/path/image.jpg'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
            'do not resolve data URL against base URL' => [
                '<base href="https://example.test/"><img alt="" src="data:image/png;base64,AAAA">',
                [
                    'count' => 1,
                    'altCount' => 0,
                    'countWithoutAlt' => 1,
                    'images' => ['data:image/png;base64,AAAA'],
                    'state' => AbstractEvaluator::STATE_RED,
                ],
            ],
        ];
    }

    public static function extractImageUrlTestDataProvider(): array
    {
        return [
            'extract from img src only' => [
                '<img src="plain-src.jpg" alt="" />',
                'plain-src.jpg',
            ],
            'extract first candidate from img srcset' => [
                '<img src="fallback.jpg" srcset="srcset-first.jpg 600w, srcset-second.jpg 1200w" alt="" />',
                'srcset-first.jpg',
            ],
            'extract candidate without descriptor from img srcset' => [
                '<img src="fallback.jpg" srcset="srcset-first.jpg, srcset-second.jpg 2x" alt="" />',
                'srcset-first.jpg',
            ],
            'extract from picture source srcset' => [
                '<picture><source srcset="picture-first.jpg 1x, picture-second.jpg 2x"><img src="fallback.jpg" alt=""></picture>',
                'picture-first.jpg',
            ],
            'picture source takes precedence over img srcset' => [
                '<picture><source srcset="picture.jpg 1x"><img src="fallback.jpg" srcset="image.jpg 1x" alt=""></picture>',
                'picture.jpg',
            ],
            'empty picture source is skipped' => [
                '<picture><source srcset=""><source srcset="picture.jpg 1x"><img src="fallback.jpg" alt=""></picture>',
                'picture.jpg',
            ],
            'picture source after img is ignored' => [
                '<picture><img src="fallback.jpg" alt=""><source srcset="too-late.jpg 1x"></picture>',
                'fallback.jpg',
            ],
            'source outside picture is ignored' => [
                '<source srcset="unrelated.jpg 1x"><img src="fallback.jpg" alt="">',
                'fallback.jpg',
            ],
            'complete data URL is retained' => [
                '<img src="data:image/png;base64,FALLBACK" srcset="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4= 1x" alt="">',
                'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=',
            ],
            'regular srcset candidate is preferred over data URL candidate' => [
                '<img src="data:image/png;base64,FALLBACK" srcset="data:image/svg+xml;base64,PHN2Zz4= 1x, regular.jpg 2x" alt="">',
                'regular.jpg',
            ],
            'regular src is preferred over data only srcset' => [
                '<img src="fallback.jpg" srcset="data:image/svg+xml;base64,PHN2Zz4= 1x" alt="">',
                'fallback.jpg',
            ],
            'regular later picture source is preferred over embedded source' => [
                '<picture><source srcset="data:image/svg+xml;base64,PHN2Zz4= 1x"><source srcset="regular.jpg 1x"><img src="fallback.jpg" alt=""></picture>',
                'regular.jpg',
            ],
            'srcset supports line breaks and ASCII whitespace' => [
                "<img src=\"fallback.jpg\" srcset=\"small.jpg 1x,\n\tlarge.jpg 2x\" alt=\"\">",
                'small.jpg',
            ],
        ];
    }
}
