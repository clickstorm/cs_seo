<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Mountpoints point here to the original URL to avoid duplicated content
 */
abstract class AbstractCanonicalTest extends AbstractFrontendTest
{
    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false
            ]
        ],
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => true,
            ],
        ],
    ];

    public function generateDataProvider(): array
    {
        return [];
    }

    /**
     * @test
     * @dataProvider generateDataProvider
     */
    public function generate(string $url, string $expectedCanonicalUrl): void
    {
        /** @var \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalResponse $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

        if ($expectedCanonicalUrl) {
            self::assertStringContainsString($expectedCanonicalUrl, $content);
        } else {
            self::assertStringNotContainsString('<link rel="canonical"', $content);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

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
