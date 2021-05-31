<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\MetaTag;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

/**
 * Abstract Test Class
 *
 * Class AbstractMetaTagTest
 */
abstract class AbstractMetaTagTest extends AbstractFrontendTest
{
    public function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [];
    }

    /**
     * @param string $url
     * @param string $expectedCanonicalUrl
     *
     * @test
     * @dataProvider ensureMetaDataAreCorrectDataProvider
     */
    public function ensureMetaDataAreCorrect(string $url, array $expectedMetaTags): void
    {
        /** @var \Nimut\TestingFramework\Http\Response $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getContent();

        foreach ($expectedMetaTags as $expectedMetaTag => $value) {
            if ($expectedMetaTag === 'title') {
                self::assertStringContainsString('<title>' . $value . '</title>', $content);
                continue;
            }

            $metaTagType = strpos($expectedMetaTag, 'og:') === 0 ? 'property' : 'name';

            if ($value) {
                if ($expectedMetaTag === 'og:image' || $expectedMetaTag === 'twitter:image') {
                    $regex = '<meta ' . $metaTagType . '="' . $expectedMetaTag . '" content=".*' . $value . '.*\.png" \/>';
                    self::assertRegExp(
                        "/{$regex}/",
                        $content
                    );
                } else {
                    self::assertStringContainsString(
                        '<meta ' . $metaTagType . '="' . $expectedMetaTag . '" content="' . $value . '" />',
                        $content
                    );
                }
            } else {
                self::assertStringNotContainsString('<meta ' . $metaTagType . '="' . $expectedMetaTag . '"', $content);
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-metatags',
            'sys_category',
            'tx_csseo_domain_model_meta',
            'sys_file',
            'sys_file_metadata',
            'sys_file_reference'
        ];

        foreach ($xmlFiles as $xmlFile) {
            $this->importDataSet($fixtureRootPath . 'Database/' . $xmlFile . '.xml');
        }

        $typoScriptFiles = [
            $fixtureRootPath . '/TypoScript/page.typoscript',
            'EXT:cs_seo/Configuration/TypoScript/setup.typoscript'
        ];

        $sitesNumbers = [1];

        foreach ($sitesNumbers as $siteNumber) {
            $sites = [];
            $sites[$siteNumber] = $fixtureRootPath . 'Sites/' . $siteNumber . '/config.yaml';
            $this->setUpFrontendRootPage($siteNumber, $typoScriptFiles, $sites);
        }
    }
}
