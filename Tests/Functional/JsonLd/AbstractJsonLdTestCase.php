<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\JsonLd;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Abstract Test Class
 *
 * Class AbstractMetaTagTest
 */
abstract class AbstractJsonLdTestCase extends AbstractFrontendTestCase
{
    public const STRING_IN_JSON_LD_TEST = 'https://www.json-ld-test.com';

    public static function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [];
    }

    #[Test]
    #[DataProvider('ensureMetaDataAreCorrectDataProvider')]
    public function ensureMetaDataAreCorrect(string $url, string $expectedJsonLd): void
    {
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        if ($expectedJsonLd !== '' && $expectedJsonLd !== '0') {
            self::assertStringContainsString(self::STRING_IN_JSON_LD_TEST, $content);
            self::assertStringContainsString('<script type="application/ld+json">' . $expectedJsonLd . '</script>', $content);
        } else {
            self::assertStringNotContainsString(self::STRING_IN_JSON_LD_TEST, $content);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSets([
            'pages-json-ld',
            'sys_category',
            'tx_csseo_domain_model_meta',
        ]);

        $typoScriptFiles = [
            $this->tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
            $this->tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
        ];

        $sitesNumbers = [1];

        $this->importTypoScript($typoScriptFiles, $sitesNumbers);
    }
}
