<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\MetaTag;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Abstract Test Class
 *
 * Class AbstractMetaTagTest
 */
abstract class AbstractMetaTagTestCase extends AbstractFrontendTestCase
{
    public static function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [];
    }

    #[Test]
    #[DataProvider('ensureMetaDataAreCorrectDataProvider')]
    public function ensureMetaDataAreCorrect(string $url, array $expectedMetaTags): void
    {
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

            $metaTagType = str_starts_with((string)$expectedMetaTag, 'og:') ? 'property' : 'name';

            if ($value) {
                if ($expectedMetaTag === 'og:image' || $expectedMetaTag === 'twitter:image') {
                    $regex = '<meta ' . $metaTagType . '="' . $expectedMetaTag . '" content=".*' . $value . '.*\.png">';
                    self::assertMatchesRegularExpression(
                        "/{$regex}/",
                        $content
                    );
                } else {
                    self::assertStringContainsString(
                        '<meta ' . $metaTagType . '="' . $expectedMetaTag . '" content="' . $value . '">',
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
            'constants' => [
                $this->tsIncludePath . 'Configuration/TypoScript/constants.typoscript',
            ],
            'setup' => [
                $this->tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
                $this->tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
            ],
        ];

        $sitesNumbers = [1];

        $this->importTypoScript($typoScriptFiles, $sitesNumbers);
    }
}
