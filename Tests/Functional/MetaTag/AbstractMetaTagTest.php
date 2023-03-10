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
     * @test
     * @dataProvider ensureMetaDataAreCorrectDataProvider
     */
    public function ensureMetaDataAreCorrect(string $url, array $expectedMetaTags): void
    {
        /** @var \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalResponse $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getBody();

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

        $this->importDataSets([
            'pages-metatags',
            'sys_category',
            'tx_csseo_domain_model_meta',
            'sys_file',
            'sys_file_metadata',
            'sys_file_reference',
        ]);

        $typoScriptFiles = [
            $this->tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
            $this->tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
        ];

        $sitesNumbers = [1];

        $this->importTypoScript($typoScriptFiles, $sitesNumbers);
    }
}
