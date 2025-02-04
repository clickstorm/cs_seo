<?php

namespace Clickstorm\CsSeo\Hook;

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class MetaTagGeneratorHook
{
    protected ?ContentObjectRenderer $cObj = null;

    public const DEFAULT_IMAGE_HEIGHT = 1200;

    public const DEFAULT_IMAGE_WIDTH = 1200;

    public function __construct()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    public function generate(array $params): void
    {
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if (!$metaData) {
            return;
        }

        // render content
        $this->renderContent($metaData);
    }

    protected function renderContent(array $metaData): void
    {
        $metaTagManagerRegistry = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $pluginSettings = GlobalsUtility::getTypoScriptSetup()['plugin.']['tx_csseo.'] ?? [];

        $ogImageUrl = $this->getOgImage($metaData, $pluginSettings);
        $twImageUrl = $this->getTwImage($metaData, $pluginSettings);

        // Crop meta description if cropDescription is active
        $emConfiguration = ConfigurationUtility::getEmConfiguration();
        if (!empty($emConfiguration['cropDescription']) && !empty($metaData['description'])) {
            $metaData['description'] = substr($metaData['description'], 0, $emConfiguration['maxDescription']) . '...';
        }

        $generators = [
            'robots' => ['value' => ''],
            'description' => ['value' => strip_tags($metaData['description'] ?? '', '<sup><sub>')],
            'og:title' => ['value' => $metaData['og_title'] ?? null],
            'og:description' => ['value' => $metaData['og_description'] ?? null],
            'og:image' => ['value' => $ogImageUrl],
            'og:type' => ['value' => $pluginSettings['social.']['openGraph.']['type']],
            'og:locale' => [
                'value' => GlobalsUtility::getLocale(),
            ],
            'twitter:title' => ['value' => $metaData['tw_title'] ?? null],
            'twitter:description' => ['value' => $metaData['tw_description'] ?? null],
            'twitter:image' => ['value' => $twImageUrl],
            'twitter:card' => ['value' => ($ogImageUrl || $twImageUrl) ? 'summary_large_image' : 'summary'],
            'twitter:creator' => ['value' => !empty($metaData['tw_creator']) ? '@' . $metaData['tw_creator'] : ''],
            'twitter:site' => ['value' => !empty($metaData['tw_site']) ? '@' . $metaData['tw_site'] : ''],
        ];

        $noIndex = !empty($metaData['no_index']) ? 'noindex' : 'index';
        $noFollow = !empty($metaData['no_follow']) ? 'nofollow' : 'follow';

        if ($noIndex === 'noindex' || $noFollow === 'nofollow') {
            $generators['robots'] = ['value' => implode(',', [$noIndex, $noFollow])];
        }

        foreach ($generators as $key => $params) {
            $manager = $metaTagManagerRegistry->getManagerForProperty($key);
            $manager->removeProperty($key);
            if (!empty($params['value'])) {
                // @extensionScannerIgnoreLine
                $manager->addProperty($key, $params['value']);
            }
        }
    }

    protected function getOgImage(array $metaData, array $pluginSettings = []): string
    {
        // og:image
        $ogImageURL = $pluginSettings['social.']['defaultImage'];
        if (!empty($metaData['og_image'])) {
            $ogImageURLFromRecord = $this->getImageOrFallback('og_image', $metaData);
            if ($ogImageURLFromRecord !== '' && $ogImageURLFromRecord !== '0') {
                $ogImageURL = $ogImageURLFromRecord;
            }
        }

        if (empty($ogImageURL)) {
            return '';
        }

        return $this->getScaledImagePath(
            $ogImageURL,
            $pluginSettings['social.']['openGraph.']['image.'] ?? []
        );
    }

    protected function getImageOrFallback(string $field, array $meta = []): string
    {
        $params = [];
        if (is_array($meta[$field])) {
            $params['table'] = $meta[$field]['table'];
            $params['field'] = $meta[$field]['field'];
            $params['uid'] = (int)$meta[$field]['uid_foreign'];
        } else {
            $params['table'] = MetaDataService::TABLE_NAME_META;
            $params['field'] = $field;
            $params['uid'] = (int)$meta['uid'];
        }

        $image = DatabaseUtility::getFile($params['table'], $params['field'], $params['uid']);

        return is_null($image) ? '' : $image->getPublicUrl();
    }

    /**
     * Return an URL to the scaled image
     */
    protected function getScaledImagePath(string $originalFile, array $imageSize): string
    {
        $conf = [
            'file' => $originalFile,
            'file.' => [
                'height' => $imageSize['height'] ?? self::DEFAULT_IMAGE_HEIGHT,
                'width' => $imageSize['width'] ?? self::DEFAULT_IMAGE_WIDTH,
            ],
        ];
        $imgUri = $this->cObj->cObjGetSingle('IMG_RESOURCE', $conf);
        $conf = [
            'parameter' => $imgUri,
            'forceAbsoluteUrl' => 1,
        ];

        return $this->cObj->typoLink_URL($conf);
    }

    protected function getTwImage(array $metaData, array $pluginSettings): string
    {
        $twImageURL = $pluginSettings['social.']['twitter.']['defaultImage'];
        if (!empty($metaData['tw_image'])) {
            $twImageURLFromRecord = $this->getImageOrFallback('tw_image', $metaData);
            if ($twImageURLFromRecord !== '' && $twImageURLFromRecord !== '0') {
                $twImageURL = $twImageURLFromRecord;
            }
        }

        if (empty($twImageURL)) {
            return '';
        }

        return $this->getScaledImagePath(
            $twImageURL,
            $pluginSettings['social.']['twitter.']['image.'] ?? []
        );
    }

    protected function escapeContent(string $content): string
    {
        return preg_replace('/\s\s+/', ' ', preg_replace('#<[^>]+>#', ' ', $content));
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
