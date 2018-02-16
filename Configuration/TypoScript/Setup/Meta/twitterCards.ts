page.headerData.654 {
	### Enable Twitter Cards ###
	35 = TEXT
	35 {
		value = summary
		override = summary_large_image
		override.if.isTrue.data = field:tx_csseo_tw_image // field:tx_csseo_og_image
		wrap = <meta name="twitter:card" content="|" />
	}

	### Twitter Cards Properties ###
	40 = COA
	40 {
		### twitter:title ###
		10 = TEXT
		10 {
			data = page:tx_csseo_tw_title
			htmlSpecialChars = 1
			wrap = <meta name="twitter:title" content="|" />
			required = 1
		}

		### twitter:description ###
		20 = TEXT
		20 {
			data = page:tx_csseo_tw_description
			htmlSpecialChars = 1
			wrap = <meta name="twitter:description" content="|" />
			required = 1
		}

		### twitter:creator ###
		30 = TEXT
		30 {
			data = page:tx_csseo_tw_creator
			htmlSpecialChars = 1
			wrap = <meta name="twitter:creator" content="@|" />
			ifEmpty = {$plugin.tx_csseo.social.twitter.creator}
			required = 1
		}

		### twitter:site ###
		35 = TEXT
		35 {
			data = page:tx_csseo_tw_site
			htmlSpecialChars = 1
			wrap = <meta name="twitter:site" content="@|" />
			ifEmpty = {$plugin.tx_csseo.social.twitter.site}
			required = 1
		}

		### twitter:image ###
		40 = TEXT
		40 {
			stdWrap.typolink {
				parameter.stdWrap {
					cObject = IMG_RESOURCE
					cObject.file {
						import {
							preUserFunc = Clickstorm\CsSeo\UserFunc\HeaderData->getSocialMediaImage
							preUserFunc.field = tx_csseo_tw_image
							ifEmpty.data = path:{$plugin.tx_csseo.social.twitter.defaultImage} // path:{$plugin.tx_csseo.social.defaultImage}
						}
						height < plugin.tx_csseo.social.twitter.image.height
						width < plugin.tx_csseo.social.twitter.image.width
					}
				}
				returnLast = url
				forceAbsoluteUrl = 1
			}
			required = 1
			wrap = <meta name="twitter:image" content="|" />
		}
	}
}
