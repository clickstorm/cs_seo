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


			robots.cObject = COA
			robots.cObject {
				10 = TEXT
				10 {
					### Exclude from search engines ###
					value = noindex
					if.isTrue.field = tx_csseo_no_index
				}

				20 = TEXT
				20 {
					### Search engines should follow links ###
					value = follow
					if.isTrue.field = tx_csseo_follow
					stdWrap.wrap = ,|
					stdWrap.if.isTrue.field = tx_csseo_no_index
				}

				30 = TEXT
				30 {
					## Exclude links from search engines ###
					value = nofollow
					if.isTrue.field = tx_csseo_no_follow
					stdWrap.wrap = ,|
					stdWrap.if.isTrue.field = tx_csseo_no_index
				}
			}
		}

		### SEO & Social Meta ###
		headerData.654 = COA
	}
	<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">
[end]