/**
* Sitemap.xml PageType
*
* @author Steefan Regniet, TechDivision GmbH
* @author Marc Hirdes, clickstorm GmbH
* @package cs_seo
* @subpackage Configuration\TypoScript\PageTypes
*/

pageCsSeoSitemap = PAGE
pageCsSeoSitemap {
	typeNum = 655

	config {
		disableAllHeaderCode = 1
		xhtml_cleaning = 0
		admPanel = 0
		debug = 0
		removeDefaultJS = 1
		removeDefaultCss = 1
		removePageCss = 1
		additionalHeaders = Content-Type:application/xml;charset=utf-8
	}

	10 = USER
	10.userFunc = Clickstorm\CsSeo\UserFunc\Sitemap->main
}