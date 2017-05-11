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
			robots.cObject = CASE
			robots.cObject {
				key.field = tx_csseo_no_index_method

				0 = TEXT
				0 {
					### Search engines should follow links ###
					value = noindex,follow
					if.isTrue.field = tx_csseo_no_index
				}

				1 = TEXT
				1 {
					## Exclude links from search engines ###
					value = noindex,nofollow
					if.isTrue.field = tx_csseo_no_index
				}
			}
		}

		### SEO & Social Meta ###
		headerData.654 = COA
	}
	<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">
[end]