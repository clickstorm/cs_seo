<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional;

use Nimut\TestingFramework\Http\Response;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Util\PHP\DefaultPhpProcess;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
abstract class AbstractFrontendTest extends FunctionalTestCase
{
    protected $failOnFailure = true;

    protected $coreExtensionsToLoad = [
        'core',
        'frontend',
        'seo'
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cs_seo'
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/Files/1920-1080.png' => 'fileadmin/1920-1080.png',
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/Files/1080-1080.png' => 'fileadmin/1080-1080.png',
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/AdditionalConfiguration.php' => 'typo3conf/AdditionalConfiguration.php'
    ];

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
}
