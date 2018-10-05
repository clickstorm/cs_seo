### Generate the meta tags ###
config {
	pageTitleFirst = 1
	pageTitleSeparator = |
	pageTitleSeparator.noTrimWrap = | | |
	titleTagFunction = Clickstorm\CsSeo\UserFunc\PageTitle->render
}

<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">