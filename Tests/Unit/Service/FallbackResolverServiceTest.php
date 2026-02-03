<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Unit\Service;

use Clickstorm\CsSeo\Service\FallbackResolverService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FallbackResolverServiceTest extends TestCase
{
    private FallbackResolverService $resolver;

    protected function setUp(): void
    {
        $this->resolver = new FallbackResolverService();
    }

    /**
     * Table-driven test to verify string-based fallback resolution.
     * Covers: direct field, multi // chain, template with {}, keep existing, trim/strip_tags, unknown fields.
     */
    #[DataProvider('resolveDataProvider')]
    public function testResolveFallbacks(array $meta, array $fallbacks, array $record, array $table, array $expected): void
    {
        $res = $this->resolver->applyFallbacks($meta, $fallbacks, $record, $table);
        foreach ($expected as $key => $value) {
            self::assertSame($value, $res[$key] ?? null);
        }
    }

    /**
     * Provides scenarios for string-based fallback resolution.
     */
    public static function resolveDataProvider(): array
    {
        $table = ['table' => 'tx_news_domain_model_news', 'uid' => 123];

        return [
            // direct field fallback
            'simple field' => [
                ['title' => ''],
                ['title' => 'seo_title'],
                ['seo_title' => 'My SEO Title'],
                $table,
                ['title' => 'My SEO Title'],
            ],
            // multi: prefer first non-empty
            'multi prefer first non-empty' => [
                ['title' => ''],
                ['title' => 'seo_title // title'],
                ['seo_title' => 'My Seo Title', 'title' => 'Base Title'],
                $table,
                ['title' => 'My Seo Title'],
            ],
            // multi: first empty, use second
            'multi fallback second' => [
                ['title' => ''],
                ['title' => 'seo_title // title'],
                ['seo_title' => '', 'title' => 'Base Title'],
                $table,
                ['title' => 'Base Title'],
            ],
            // template with curly braces
            'template with braces' => [
                ['description' => ''],
                ['description' => 'Foo: {seo_description} - {title}'],
                ['seo_description' => 'Awesome', 'title' => 'News'],
                $table,
                ['description' => 'Foo: Awesome - News'],
            ],
            // keep existing non-empty meta value
            'keep existing value' => [
                ['title' => 'Keep Me'],
                ['title' => 'seo_title'],
                ['seo_title' => 'New'],
                $table,
                ['title' => 'Keep Me'],
            ],
            // sanitize: trim and strip tags
            'trim and strip tags' => [
                ['description' => ''],
                ['description' => 'desc'],
                ['desc' => '  <b>Hello</b> World  '],
                $table,
                ['description' => 'Hello World'],
            ],
            // unknown fallback field
            'unknown field' => [
                ['title' => ''],
                ['title' => 'unknown_field'],
                [],
                ['table' => 'tx_any', 'uid' => 1],
                ['title' => ''],
            ],
        ];
    }

    public function testImageSpecialCaseBuildsArray(): void
    {
        $meta = ['og_image' => ''];
        $fallbacks = ['og_image' => 'image'];
        $record = ['image' => 'fileRef'];
        $table = ['table' => 'tx_news_domain_model_news', 'uid' => 123];

        $res = $this->resolver->applyFallbacks($meta, $fallbacks, $record, $table);

        self::assertIsArray($res['og_image']);
        self::assertSame([
            'field' => 'image',
            'table' => 'tx_news_domain_model_news',
            'uid_foreign' => 123,
        ], $res['og_image']);
    }

    public function testExistingMetaIsNotOverwritten(): void
    {
        $meta = ['title' => 'Keep Me'];
        $fallbacks = ['title' => 'seo_title'];
        $record = ['seo_title' => 'New'];
        $table = ['table' => 'tx_news_domain_model_news', 'uid' => 123];

        $res = $this->resolver->applyFallbacks($meta, $fallbacks, $record, $table);
        self::assertSame('Keep Me', $res['title']);
    }

    public function testTrimsAndStripsHtml(): void
    {
        $meta = ['description' => ''];
        $fallbacks = ['description' => 'desc'];
        $record = ['desc' => '  <b>Hello</b> World  '];
        $table = ['table' => 'tx_news_domain_model_news', 'uid' => 123];

        $res = $this->resolver->applyFallbacks($meta, $fallbacks, $record, $table);
        self::assertSame('Hello World', $res['description']);
    }

    public function testUnknownFieldsAreIgnoredGracefully(): void
    {
        $meta = ['title' => ''];
        $fallbacks = ['title' => 'unknown_field'];
        $record = [];
        $table = ['table' => 'tx_any', 'uid' => 1];

        $res = $this->resolver->applyFallbacks($meta, $fallbacks, $record, $table);

        self::assertArrayHasKey('title', $res);
        self::assertSame('', $res['title']);
    }
}
