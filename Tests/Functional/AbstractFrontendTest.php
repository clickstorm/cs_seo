<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\Response;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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
        'seo',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cs_seo',
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/Files/1920-1080.png' => 'fileadmin/1920-1080.png',
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/Files/1080-1080.png' => 'fileadmin/1080-1080.png',
        'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/AdditionalConfiguration.php' => 'typo3conf/AdditionalConfiguration.php',
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
        $request = (new InternalRequest())->withUri(new Uri($url));

        if (isset($_SERVER['XDG_SESSION_ID'])) {
            $request->withHeader('XDEBUG_SESSION_START', 'PHPSTORM');
        }

        return $this->executeFrontendSubRequest($request);
    }

    protected function setUpSites($pageId, array $sites)
    {
        foreach ($sites as $identifier => $file) {
            $path = Environment::getConfigPath() . '/sites/' . $identifier . '/';
            $target = $path . 'config.yaml';
            if (!file_exists($target)) {
                GeneralUtility::mkdir_deep($path);
                if (!file_exists($file)) {
                    $file = GeneralUtility::getFileAbsFileName($file);
                }
                $fileContent = file_get_contents($file);
                $fileContent = str_replace('\'{rootPageId}\'', (string)$pageId, $fileContent);
                GeneralUtility::writeFile($target, $fileContent);
            }
        }
        // Ensure that no other site configuration was cached before
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('core');
        if ($cache->has('sites-configuration')) {
            $cache->remove('sites-configuration');
        }
    }
}
