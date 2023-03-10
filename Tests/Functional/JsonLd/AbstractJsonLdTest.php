<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\JsonLd;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

/**
 * Abstract Test Class
 *
 * Class AbstractMetaTagTest
 */
abstract class AbstractJsonLdTest extends AbstractFrontendTest
{
    public const STRING_IN_JSON_LD_TEST = 'https://www.json-ld-test.com';

    public function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [];
    }

    /**
     * @test
     * @dataProvider ensureMetaDataAreCorrectDataProvider
     */
    public function ensureMetaDataAreCorrect(string $url, string $expectedJsonLd): void
    {
        /** @var \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalResponse $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        if ($expectedJsonLd) {
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
