<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Mountpoints point here to the original URL to avoid duplicated content
 */
abstract class AbstractCanonicalTestCase extends AbstractFrontendTestCase
{
    public static function generateDataProvider(): array
    {
        return [];
    }

    #[Test]
    #[DataProvider('generateDataProvider')]
    public function generate(string $url, string $expectedCanonicalUrl): void
    {
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        if ($expectedCanonicalUrl !== '' && $expectedCanonicalUrl !== '0') {
            self::assertStringContainsString($expectedCanonicalUrl, $content);
        } else {
            self::assertStringNotContainsString('<link rel="canonical"', $content);
        }
    }

    protected function setUp(): void
    {
        $this->importDataSets([
            'pages-canonical',
            'sys_category',
            'tx_csseo_domain_model_meta',
        ]);

        $typoScriptFiles = [
            $this->tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
            $this->tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
        ];

        $sitesNumbers = ['1', '100', '200'];

        $this->importTypoScript($typoScriptFiles, $sitesNumbers);
    }
}
