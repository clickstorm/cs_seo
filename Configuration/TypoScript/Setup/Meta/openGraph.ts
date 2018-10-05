page.meta {
	### og:type ###
	og:type < plugin.tx_csseo.social.openGraph.type

	### og:site_name ###
	og:site_name.data = TSFE:tmpl|sitetitle

	### og:image ###
	og:image.stdWrap.typolink {
		parameter.stdWrap {
			cObject = IMG_RESOURCE
			cObject.file {
				import {
					preUserFunc = Clickstorm\CsSeo\UserFunc\HeaderData->getSocialMediaImage
					preUserFunc.field = og_image
					ifEmpty.data = path:{$plugin.tx_csseo.social.defaultImage}
				}
				height = {$plugin.tx_csseo.social.openGraph.image.height}
				width = {$plugin.tx_csseo.social.openGraph.image.width}
			}
		}
		returnLast = url
		forceAbsoluteUrl = 1
	}
}
