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
abstract class AbstractCanonicalTest extends FunctionalTestCase
{
    protected $failOnFailure = false;

    protected $coreExtensionsToLoad = [
        'core',
        'frontend',
        'seo'
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cs_seo'
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/AdditionalConfiguration.php' => 'typo3conf/AdditionalConfiguration.php'
    ];

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