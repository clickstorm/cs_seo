<?php
namespace Clickstorm\CsSeo\UserFunc;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the structured Data for Google SiteSearch and Breadcrumb
 *
 * @package Clickstorm\CsSeo\UserFunc
 * @see https://developers.google.com/search/docs/guides/intro-structured-data
 */
class StructuredData
{

    /**
     * @var \Clickstorm\CsSeo\Utility\TSFEUtility $tsfeUtility
     */
    public $tsfeUtility;

    public function __construct()
    {
        $this->tsfeUtility =
            GeneralUtility::makeInstance(\Clickstorm\CsSeo\Utility\TSFEUtility::class, $GLOBALS['TSFE']->id);
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
            'additionalParams' => '&' . $conf['userFunc.']['searchterm'] . '='
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

        return $this->wrapWithLd(json_encode($siteSearch));
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
        return '<script type="application/ld+json">' . $content . '</script>';
    }

    /**
     * Returns the json for the serps breadcrumb
     *
     * @param $conf
     * @param $content
     *
     * @return string
     */
    public function getBreadcrumb($conf, $content)
    {
        /** @var  \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController[] $GLOBALS */
        $id = $GLOBALS['TSFE']->id;
        if (!empty($GLOBALS['TSFE']->MP)) {
            // mouting point page - generate breadcrumb for the mounting point reference page instead
            list(,$id) = explode('-', $GLOBALS['TSFE']->MP);
        }
        $rootline = $pageRepository->getRootLine($id);

        $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $siteLinks = [];

        foreach (array_reverse($rootline) as $index => $page) {
            $typoLinkConf = [
                'parameter' => $page['uid'],
                'forceAbsoluteUrl' => 1
            ];

            if ($GLOBALS['TSFE']->sys_language_uid > 0) {
                $page = $pageRepository->getPageOverlay($page);
            }

            $siteLinks[] = [
                'link' => $cObject->typoLink_URL($typoLinkConf),
                'name' => $page['title'],
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
            'itemListElement' => $breadcrumbItems
        ];

        return $this->wrapWithLd(json_encode($structuredBreadcrumb));
    }
}
