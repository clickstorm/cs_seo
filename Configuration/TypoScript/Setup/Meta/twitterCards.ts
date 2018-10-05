page.meta {
	### Enable Twitter Cards ###
	twitter:card = summary
	twitter:card {
		override = summary_large_image
		override.if.isTrue.data = field:twitter_image // field:og_image
	}

	### twitter:creator ###
	twitter:creator {
		data = page:tx_csseo_tw_creator
		htmlSpecialChars = 1
		wrap = <meta name="twitter:creator" content="@|" />
		ifEmpty = {$plugin.tx_csseo.social.twitter.creator}
	}

	### twitter:site ###
	twitter:site {
		data = page:tx_csseo_tw_site
		htmlSpecialChars = 1
		wrap = <meta name="twitter:site" content="@|" />
		ifEmpty = {$plugin.tx_csseo.social.twitter.site}
	}

	### twitter:image ###
	twitter:image.stdWrap.typolink {
		parameter.stdWrap {
			cObject = IMG_RESOURCE
			cObject.file {
				import {
					preUserFunc = Clickstorm\CsSeo\UserFunc\HeaderData->getSocialMediaImage
					preUserFunc.field = twitter_image
					ifEmpty.data = path:{$plugin.tx_csseo.social.twitter.defaultImage} // path:{$plugin.tx_csseo.social.defaultImage}
				}
				height < plugin.tx_csseo.social.twitter.image.height
				width < plugin.tx_csseo.social.twitter.image.width
			}
		}
		returnLast = url
		forceAbsoluteUrl = 1
	}
}
