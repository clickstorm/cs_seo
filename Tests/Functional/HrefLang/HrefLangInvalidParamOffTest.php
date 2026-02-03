<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

/**
 * remove invalid parameter from url
 */
class HrefLangInvalidParamOffTest extends AbstractHrefLangTestCase
{
    protected function setUp(): void
    {
        $this->importDataSets([
            'pages-hreflang',
        ]);

        $typoScriptFiles = [
            $this->tsIncludePath . 'Tests/Functional/Fixtures/TypoScript/page.typoscript',
            $this->tsIncludePath . 'Configuration/TypoScript/setup.typoscript',
        ];

        $sitesNumbers = [1];
        $this->importTypoScript($typoScriptFiles, $sitesNumbers);
    }

    public static function checkHrefLangOutputDataProvider(): array
    {
        return [
            'Remove invalid parameter foo=bar' => [
                'http://localhost/hello?foo=bar',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen"',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello"',
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom?foo=bar"',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom?foo=bar"',
                    '<link rel="alternate" href="http://localhost/nl/welkom?foo=bar"',
                ],
            ],
            'Keep valid speaking parameter' => [
                'http://localhost/hello/keep-param/1',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello/keep-param/1"',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen/keep-param/1"',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen/keep-param/1"',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello/keep-param/1"',
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom/keep-param/1"',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom/keep-param/1"',
                    '<link rel="alternate" href="http://localhost/nl/welkom/keep-param/1"',
                ],
            ],
        ];
    }
}
