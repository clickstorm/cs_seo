### Generate the meta tags ###

config {
	pageTitleFirst = 1
	pageTitleSeparator = |
	pageTitleSeparator.noTrimWrap = | | |
}

### Title Tag ###
[userFunc = Clickstorm\CsSeo\UserFunc\HeaderData::checkSeoGP()]
	config.noPageTitle = 2
	page.headerData.654 = USER
	page.headerData.654.userFunc = Clickstorm\CsSeo\UserFunc\HeaderData->getMetaTags
[else]
	config.titleTagFunction = Clickstorm\CsSeo\UserFunc\PageTitle->render

	page {

		meta {
			### General Meta Tags ###
			description {
				field = description
				htmlSpecialChars = 1
			}

			### Exclude from search engines ###
			robots = noindex,follow
			robots.if.isTrue.field = tx_csseo_no_index
		}

		### SEO & Social Meta ###
		headerData.654 = COA
	}
	<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">
[end]