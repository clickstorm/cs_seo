/**
* Preview Wizard PageType
*
* @author Marc Hirdes, clickstorm GmbH
* @package cs_seo
* @subpackage Configuration\TypoScript\PageTypes
*/

pageCsSeo = PAGE
pageCsSeo {
	typeNum = 654

	config {
		disableAllHeaderCode = 1
		xhtml_cleaning = 0
		admPanel = 0
		debug = 0
		index_enable = 0
		removeDefaultJS = 1
		removeDefaultCss = 1
		removePageCss = 1
		INTincScript_ext.pagerender = 1
	}

	meta < page.meta

	10 = TEXT
	10.value = Page Properties loaded
}