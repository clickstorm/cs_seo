<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

/**
 * different behaviour then core with another x-default and fix from https://forge.typo3.org/issues/94207
 *
 * Class HrefLangDifferentXDefault
 * @package Clickstorm\CsSeo\Tests\Functional\HrefLang
 */
class HrefLangDifferentXDefaultTest extends AbstractHrefLangTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-hreflang'
        ];

        foreach ($xmlFiles as $xmlFile) {
            $this->importDataSet($fixtureRootPath . 'Database/' . $xmlFile . '.xml');
        }

        $typoScriptFiles = [
            $fixtureRootPath . '/TypoScript/page.typoscript',
            'EXT:cs_seo/Configuration/TypoScript/setup.typoscript'
        ];

        $sites = [];
        $sites[1] = $fixtureRootPath . 'Sites/csseo-xdefault.yaml';
        $this->setUpFrontendRootPage(1, $typoScriptFiles, $sites);
    }

    /**
     * @return array
     */
    public function checkHrefLangOutputDataProvider(): array
    {
        return [
            'No translation available, so only hreflang tags expected for default language and fallback languages' => [
                'http://localhost/',
                [
                ],
                [
                    '<link rel="alternate" hreflang="'
                ]
            ],
            'English page, with German translation' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/willkommen"/>',
                ],
                []
            ],
            'German page, with English translation and English default' => [
                'http://localhost/de/willkommen',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/willkommen"/>',
                ],
                []
            ],
            'English page, with German and Dutch translation, without Dutch hreflang config' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/willkommen"/>',
                ],
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" href="http://localhost/nl/welkom"/>'
                ]
            ],
            'Dutch page, with German and English translation, without Dutch hreflang config' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/willkommen"/>',
                ],
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" href="http://localhost/nl/welkom"/>'
                ]
            ],
            'English page with canonical' => [
                'http://localhost/contact',
                [
                ],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Translated record (de-CH) with canonical call default language' => [
                'http://localhost/about',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/about"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/uber"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/uber"/>',
                ],
                [
                    '<link rel="alternate" hreflang="de-CH"',
                ]
            ],
            'Translated record de-CH) with canonical call language with canonical' => [
                'http://localhost/de-ch/uber',
                [
                ],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Swiss german page with fallback to German, without content' => [
                'http://localhost/de-ch/produkte',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/products"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/de/produkte"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/produkte"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/produkte"/>',
                ],
                []
            ],
            'Languages with fallback should have hreflang even when page record is not translated, strict languages without translations shouldnt' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen"/>',
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                ]
            ],
            'Pages with disabled hreflang generation should not render any hreflang tag' => [
                'http://localhost/no-hreflang',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Translated pages with disabled hreflang generation in original language should not render any hreflang tag' => [
                'http://localhost/de/kein-hreflang',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Page with no_index' => [
                'http://localhost/no-index',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Page with content_from_pid' => [
                'http://localhost/content-from-pid',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
        ];
    }
}
