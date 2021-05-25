<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

use Nimut\TestingFramework\Http\Response;
use PHPUnit\Util\PHP\DefaultPhpProcess;
use TYPO3\CMS\Core\Tests\Functional\SiteHandling\SiteBasedTestTrait;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
class CanonicalGeneratorTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'core',
        'frontend',
        'seo'
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cs_seo'
    ];

    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => '1',
            ],
        ]
    ];

    public function generateDataProvider(): array
    {
        return [
            // core tests
            'uid: 1 with canonical_link' => [
                'http://localhost/',
                '<link rel="canonical" href="http://localhost/"/>' . chr(10),
            ],
            'uid: 2 with canonical_link' => [
                'http://localhost/dummy-1-2',
                '<link rel="canonical" href="http://localhost/dummy-1-2"/>' . chr(10),
            ],
            'uid: 3 with canonical_link AND content_from_pid = 2' => [
                'http://localhost/dummy-1-3',
                '<link rel="canonical" href="http://localhost/dummy-1-2"/>' . chr(10),
            ],
            'uid: 4 without canonical_link AND content_from_pid = 2' => [
                'http://localhost/dummy-1-4',
                '<link rel="canonical" href="http://localhost/dummy-1-2"/>' . chr(10),
            ],
            'uid: 5 without canonical_link AND without content_from_pid set' => [
                'http://localhost/dummy-1-2-5',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5"/>' . chr(10),
            ],
            'uid: 8 without canonical_link AND content_from_pid = 9 (but target page is hidden) results in no canonical' => [
                'http://localhost/dummy-1-2-8',
                '',
            ],
            'uid: 10 no index' => [
                'http://localhost/dummy-1-2-10',
                ''
            ],
            'uid: 11 with mount_pid_ol = 0' => [
                'http://localhost/dummy-1-2-11',
                '<link rel="canonical" href="http://localhost/dummy-1-2-11"/>' . chr(10),
            ],
            'uid: 12 with mount_pid_ol = 1' => [
                'http://localhost/dummy-1-2-12',
                '<link rel="canonical" href="http://localhost/dummy-1-2-12"/>' . chr(10),
            ],
            'subpage of page with mount_pid_ol = 0' => [
                'http://localhost/dummy-1-2-11/subpage-of-new-root',
                '<link rel="canonical" href="http://localhost/dummy-1-2-11/subpage-of-new-root"/>' . chr(10),
            ],
            'subpage of page with mount_pid_ol = 1' => [
                'http://localhost/dummy-1-2-12/subpage-of-new-root',
                '<link rel="canonical" href="http://localhost/dummy-1-2-12/subpage-of-new-root"/>' . chr(10),
            ],
            'uid: 14 typoscript setting config.disableCanonical' => [
                'http://localhost/no-canonical',
                ''
            ],
            // cs_seo tests
            'uid: 5 without canonical_link AND without content_from_pid set and invalid param' => [
                'http://localhost/dummy-1-2-5?foo=bar',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5"/>' . chr(10),
            ],
        ];
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
            false
        );

        if ($expectedCanonicalUrl) {
            self::assertStringContainsString($expectedCanonicalUrl, (string)$response->getContent());
        } else {
            self::assertStringNotContainsString('<link rel="canonical"', (string)$response->getContent());
        }
    }

    /**
     * copied and modified from testing frame work, to force an URL
     *
     * @param string $url
     * @param bool $failOnFailure
     * @param int $frontendUserId
     * @return Response
     */
    protected function getFrontendResponseFromUrl($url, $failOnFailure = true, $frontendUserId = 0)
    {
        $arguments = [
            'documentRoot' => $this->getInstancePath(),
            'requestUrl' => $url,
        ];

        $template = new \Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments' => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot' => ORIGINAL_ROOT . '../vendor/nimut/testing-framework/',
            ]
        );

        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === Response::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        $response = new Response($result['status'], $result['content'], $result['error']);

        return $response;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-canonical'
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