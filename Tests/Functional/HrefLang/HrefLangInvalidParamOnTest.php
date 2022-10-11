<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

/**
 * keep invalid parameter in URL - current TYPO3 core behaviour
 */
class HrefLangInvalidParamOnTest extends AbstractHrefLangTest
{
    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => false,
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-hreflang',
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

    /**
     * @return array
     */
    public function checkHrefLangOutputDataProvider(): array
    {
        return [
            'Remove invalid parameter foo=bar' => [
                'http://localhost/hello?foo=bar',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello?foo=bar',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen?foo=bar',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen?foo=bar',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello?foo=bar',
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom?foo=bar',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom?foo=bar',
                    '<link rel="alternate" href="http://localhost/nl/welkom?foo=bar',
                ],
            ],
        ];
    }
}
