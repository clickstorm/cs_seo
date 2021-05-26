<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Clickstorm\CsSeo\Tests\Functional\MetaTag;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

/**
 * Functional test for the DataHandler
 */
class MetaTagTest extends AbstractFrontendTest
{
    public function ensureMetaDataAreCorrectDataProvider(): array
    {
        return [
            'page 1: with title and description' => [
                'http://localhost/',
                [
                    'title' => 'Title 1',
                    'description' => 'Description 1',
                    'og:type' => '{$plugin.tx_csseo.social.openGraph.type}',
                    'twitter:card' => 'summary',
                    'twitter:creator' => '@{$plugin.tx_csseo.social.twitter.creator}',
                    'twitter:site' => '@{$plugin.tx_csseo.social.twitter.site}',
                    'robots' => ''
                ]
            ],
            'page 2: with browser title and open graph' => [
                'http://localhost/page-with-og',
                [
                    'title' => 'Browser Title 2',
                    'description' => 'Description 2',
                    'og:title' => 'OG Title 2',
                    'og:description' => 'OG Description 2',
                    'og:image' => 'http://localhost/1920-1080.png'
                ]
            ],
            'page 3: with twitter cards' => [
                'http://localhost/page-with-twitter-cards',
                [
                    'title' => 'Title 3',
                    'description' => 'Description 3',
                    'twitter:title' => 'TW Title 3',
                    'twitter:description' => 'TW Description 3',
                    'twitter:image' => 'http://localhost/1080-1080.png',
                    'twitter:creator' => '@TW Creator 3',
                    'twitter:site' => '@TW Site 3'
                ]
            ],
            'page 4: with no index' => [
                'http://localhost/page-no-index',
                [
                    'title' => 'Title 4',
                    'robots' => 'noindex,follow'
                ]
            ],
            'page 5: with no follow' => [
                'http://localhost/page-no-follow',
                [
                    'title' => 'Title 5',
                    'robots' => 'index,nofollow'
                ]
            ],
            'page 6: with no index, no follow' => [
                'http://localhost/page-no-index-no-follow',
                [
                    'title' => 'Title 6',
                    'robots' => 'noindex,nofollow'
                ]
            ],
        ];
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
                self::assertStringContainsString('<meta ' . $metaTagType . '="' . $expectedMetaTag . '" content="' . $value . '" />',
                    $content);
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
            'sys_file_storage',
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