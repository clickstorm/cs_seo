<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
abstract class AbstractCanonicalTest extends AbstractFrontendTest
{
    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => true,
            ],
        ]
    ];

    public function generateDataProvider(): array
    {
        return [];
    }

    /**
     * @param string $url
     * @param string $expectedCanonicalUrl
     *
     * @test
     * @dataProvider generateDataProvider
     */
    public function generate(string $url, string $expectedCanonicalUrl): void
    {
        /** @var \Nimut\TestingFramework\Http\Response $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        if ($expectedCanonicalUrl) {
            self::assertStringContainsString($expectedCanonicalUrl, (string)$response->getContent());
        } else {
            self::assertStringNotContainsString('<link rel="canonical"', (string)$response->getContent());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-canonical',
            'sys_category',
            'tx_csseo_domain_model_meta'
        ];

        foreach ($xmlFiles as $xmlFile) {
            $this->importDataSet($fixtureRootPath . 'Database/' . $xmlFile . '.xml');
        }

        $typoScriptFiles = [
            $fixtureRootPath . '/TypoScript/page.typoscript',
            'EXT:cs_seo/Configuration/TypoScript/setup.typoscript'
        ];

        $sitesNumbers = [1, 100, 200];

        foreach ($sitesNumbers as $siteNumber) {
            $sites = [];
            $sites[$siteNumber] = $fixtureRootPath . 'Sites/' . $siteNumber . '/config.yaml';
            $this->setUpFrontendRootPage($siteNumber, $typoScriptFiles, $sites);
        }
    }
}
