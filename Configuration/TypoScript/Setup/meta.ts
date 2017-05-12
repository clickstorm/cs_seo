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
			robots {
				cObject = COA
				cObject {
					10 = TEXT
					10 {
						value = index
						override = noindex
						override.if.isTrue.field = tx_csseo_no_index
						wrap = |,
					}

					20 = TEXT
					20 {
						value = follow
						override = nofollow
						override.if.isTrue.field = tx_csseo_no_follow
					}
				}
				if.isTrue.data = field:tx_csseo_no_index // field:tx_csseo_no_follow
			}
		}

		### SEO & Social Meta ###
		headerData.654 = COA
	}
	<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Meta/" extensions="ts">
[end]