### Generate the meta tags ###
config {
	pageTitleFirst = 1
	pageTitleSeparator = |
	pageTitleSeparator.noTrimWrap = | | |
	titleTagFunction = Clickstorm\CsSeo\UserFunc\PageTitle->render
	noPageTitle = 2
}

### add meta data ###
page.headerData.654 = COA
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">

### override if detail view ###
page.headerData.654.stdWrap.override {
	cObject = USER
	cObject.userFunc = Clickstorm\CsSeo\UserFunc\HeaderData->getMetaTags
	if.isTrue.cObject = USER
	if.isTrue.cObject.userFunc = Clickstorm\CsSeo\UserFunc\HeaderData->checkSeoGP
}