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
    const STRING_IN_JSON_LD_TEST = 'https://www.json-ld-test.com';

    public function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [];
    }

    /**
     * @param string $url
     * @param string $expectedJsonLd
     *
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

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-json-ld',
            'sys_category',
            'tx_csseo_domain_model_meta',
        ];

        foreach ($xmlFiles as $xmlFile) {
            $this->importDataSet($fixtureRootPath . 'Database/' . $xmlFile . '.xml');
        }

        $tsIncludePath = 'EXT:cs_seo/';

        $typoScriptFiles = [
            $tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
            $tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
        ];

        $sitesNumbers = [1];

        foreach ($sitesNumbers as $siteNumber) {
            $sites = [];
            $sites[$siteNumber] = $fixtureRootPath . 'Sites/' . $siteNumber . '/config.yaml';
            $this->setUpSites($siteNumber, $sites);
            $this->setUpFrontendRootPage($siteNumber, $typoScriptFiles);
        }
    }
}
