<?php

namespace Clickstorm\CsSeo\UserFunc;

use Clickstorm\CsSeo\Utility\GlobalsUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Clickstorm\CsSeo\Service\MetaDataService;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Alexander Wahl <alexander.wahl@setusoft.de>, SETU GmbH
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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the structured Data for Google SiteSearch and Breadcrumb
 *
 * @see https://developers.google.com/search/docs/guides/intro-structured-data
 */
class StructuredData
{
    /**
     * return the content of field tx_csseo_json_ld from pages or field json_ld from record
     *
     * @param string $content
     * @param array $conf
     *
     * @retrun string
     */
    public function getJsonLdOfPageOrRecord($content, $conf)
    {
        $metaData = GeneralUtility::makeInstance(MetaDataService::class)->getMetaData();
        $jsonLd = $GLOBALS['TSFE']->page['tx_csseo_json_ld'];

        // overwrite json ld with record metadata
        if ($metaData) {
            $jsonLd = $metaData['json_ld'];
        }

        // if empty return nothing
        if (empty($jsonLd)) {
            return '';
        }

        // Try to decode the JSON string to ensure it's valid
        $tempJson = json_decode($jsonLd, true);

        // Check if decoding was successful
        if (json_last_error() === JSON_ERROR_NONE) {

            // Re-encode the array to sanitize any unexpected content
            $safeJson = json_encode($tempJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $this->wrapWithLd($safeJson);
        }

        return '<!-- Invalid JSON-LD -->';
    }

    /**
     * Wraps $content with Json declaration
     *
     * @param $content
     *
     * @return string
     */
    protected function wrapWithLd($content)
    {
        // Escape special characters to prevent breaking out of the script tag
        return '<script type="application/ld+json">' . htmlspecialchars($content, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</script>';
    }

    /**
     * Returns the json for the siteSearch
     *
     * @return bool|string siteSearch
     */
    public function getSiteSearch($content, $conf)
    {
        $homepage = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');

        $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $typoLinkConf = [
            'parameter' => $conf['userFunc.']['pid'],
            'forceAbsoluteUrl' => 1,
            'additionalParams' => '&' . $conf['userFunc.']['searchterm'] . '=',
        ];

        $siteSearchUrl = $cObject->typoLink_URL($typoLinkConf);

        $siteSearch = [
            '@context' => 'http://schema.org',
            '@type' => 'WebSite',
            'url' => $homepage . '/',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $siteSearchUrl . '{search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return $this->wrapWithLd(json_encode($siteSearch, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Returns the json for the serps breadcrumb
     *
     * @param $conf
     * @param $content
     *
     * @return string
     * @throws \Exception
     */
    public function getBreadcrumb($conf, $content)
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');

        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController[] $GLOBALS */
        $id = $GLOBALS['TSFE']->id;
        if (!empty($GLOBALS['TSFE']->MP) && preg_match('/^\\d+\\-(\\d+)$/', $GLOBALS['TSFE']->MP, $match)) {
            // mouting point page - generate breadcrumb for the mounting point reference page instead
            $id = (int)($match[1]);
        }

        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $id)->get();

        // remove DOKTYPE_SYSFOLDER from rootline
        $rootline = array_values(array_filter($rootline, function ($item) {
            return $item['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER;
        }));

        // prevent output of empty rootline
        if (count($rootline) < 2) {
            return '';
        }

        $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $siteLinks = [];

        foreach (array_reverse($rootline) as $index => $page) {
            $typoLinkConf = [
                'parameter' => $page['uid'],
                'forceAbsoluteUrl' => 1,
            ];

            if ($languageAspect->getId() > 0) {
                $page = $pageRepository->getPageOverlay($page, $languageAspect->getId());
            }

            $siteLinks[] = [
                'link' => $cObject->typoLink_URL($typoLinkConf),
                'name' => $page['nav_title'] ?: $page['title'],
            ];
        }

        $breadcrumbItems = [];
        // remove the last element because it's the current page itself and this should NOT be included
        // into the structured breadcrumb
        array_pop($siteLinks);

        foreach ($siteLinks as $index => $pInfo) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@id' => $pInfo['link'],
                    'name' => $pInfo['name'],
                ],
            ];
        }

        // assemble the json output
        $structuredBreadcrumb = [
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        return $this->wrapWithLd(json_encode($structuredBreadcrumb, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
