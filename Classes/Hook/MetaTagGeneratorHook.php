<?php

namespace Clickstorm\CsSeo\Hook;

use Clickstorm\CsSeo\Service\MetaDataService;
use Clickstorm\CsSeo\Utility\ConfigurationUtility;
use Clickstorm\CsSeo\Utility\DatabaseUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Marc Hirdes <hirdes@clickstorm.de>, clickstorm GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class MetaTagGeneratorHook
{
    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    public function __construct()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * @param array $params
     */
    public function generate(array $params)
    {
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();

        if (!$metaData) {
            return;
        }

        // render content
        $this->renderContent($metaData);
    }

    /**
     * @param array $metaData
     */
    protected function renderContent($metaData): void
    {
        $metaTagManagerRegistry = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $pluginSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_csseo.'];

        $ogImageUrl = $this->getOgImage($metaData, $pluginSettings);
        $twImageUrl = $this->getTwImage($metaData, $pluginSettings);

        // Crop meta description if cropDescription is active
        $emConfiguration = ConfigurationUtility::getEmConfiguration();
        if ($emConfiguration['cropDescription']) {
            $metaData['description'] = substr($metaData['description'], 0, $emConfiguration['maxDescription']) . '...';
        }

        $generators = [
            'robots' => ['value' => ''],
            'description' => ['value' => strip_tags($metaData['description'], '<sup><sub>')],
            'og:title' => ['value' => $metaData['og_title']],
            'og:description' => ['value' => $metaData['og_description']],
            'og:image' => ['value' => $ogImageUrl],
            'og:type' => ['value' => $pluginSettings['social.']['openGraph.']['type']],
            'og:locale' => ['value' => strstr($GLOBALS['TSFE']->config['config']['locale_all'], '.', true)],
            'twitter:title' => ['value' => $metaData['tw_title']],
            'twitter:description' => ['value' => $metaData['tw_description']],
            'twitter:image' => ['value' => $twImageUrl],
            'twitter:card' => ['value' => ($ogImageUrl || $twImageUrl) ? 'summary_large_image' : 'summary'],
            'twitter:creator' => ['value' => $metaData['tw_creator'] ?: $pluginSettings['social.']['twitter.']['creator']],
            'twitter:site' => ['value' => $metaData['tw_site'] ?: $pluginSettings['social.']['twitter.']['site']],
        ];

        $noIndex = ((bool)$metaData['no_index']) ? 'noindex' : 'index';
        $noFollow = ((bool)$metaData['no_follow']) ? 'nofollow' : 'follow';

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

    /**
     * @param array $metaData
     * @param array $pluginSettings
     * @return string
     */
    protected function getOgImage(array $metaData, array $pluginSettings): string
    {
        // og:image
        $ogImageURL = $pluginSettings['social.']['defaultImage'];
        if ($metaData['og_image']) {
            $ogImageURLFromRecord = $this->getImageOrFallback('og_image', $metaData);
            if ($ogImageURLFromRecord) {
                $ogImageURL = $ogImageURLFromRecord;
            }
        }

        if (empty($ogImageURL)) {
            return '';
        }

        return $this->getScaledImagePath(
            $ogImageURL,
            $pluginSettings['social.']['openGraph.']['image.']
        );
    }

    /**
     * @param string $field
     * @param array $meta
     *
     * @return string the image path
     */
    protected function getImageOrFallback($field, $meta)
    {
        $params = [];
        if (is_array($meta[$field])) {
            $params['table'] = $meta[$field]['table'];
            $params['field'] = $meta[$field]['field'];
            $params['uid'] = $meta[$field]['uid_foreign'];
        } else {
            $params['table'] = MetaDataService::TABLE_NAME_META;
            $params['field'] = 'tx_csseo_' . $field;
            $params['uid'] = $meta['uid'];
        }

        $image = DatabaseUtility::getFile($params['table'], $params['field'], $params['uid']);
        if ($image) {
            return $image->getPublicUrl();
        }
    }

    /**
     * Return an URL to the scaled image
     *
     * @param string $originalFile uid or path of the file
     * @param array $imageSize width and height as keys
     *
     * @return string
     */
    protected function getScaledImagePath(string $originalFile, array $imageSize): string
    {
        $conf = [
            'file' => $originalFile,
            'file.' => [
                'height' => $imageSize['height'],
                'width' => $imageSize['width']
            ]
        ];
        $imgUri = $this->cObj->cObjGetSingle('IMG_RESOURCE', $conf);
        $conf = [
            'parameter' => $imgUri,
            'forceAbsoluteUrl' => 1
        ];

        return $this->cObj->typoLink_URL($conf);
    }

    /**
     * @param array $metaData
     * @param array $pluginSettings
     * @return string
     */
    protected function getTwImage(array $metaData, array $pluginSettings): string
    {
        $twImageURL = $pluginSettings['social.']['twitter.']['defaultImage'];
        if ($metaData['tw_image']) {
            $twImageURLFromRecord = $this->getImageOrFallback('tw_image', $metaData);
            if ($twImageURLFromRecord) {
                $twImageURL = $twImageURLFromRecord;
            }
        }

        if (empty($twImageURL)) {
            return '';
        }

        return $this->getScaledImagePath(
            $twImageURL,
            $pluginSettings['social.']['twitter.']['image.']
        );
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function escapeContent($content)
    {
        return preg_replace('/\s\s+/', ' ', preg_replace('#<[^>]+>#', ' ', $content));
    }
}
