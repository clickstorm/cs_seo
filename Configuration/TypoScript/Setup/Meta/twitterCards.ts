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

		### twitter:author ###
		30 = TEXT
		30 {
			data = page:tx_csseo_tw_creator
			htmlSpecialChars = 1
			wrap = <meta name="twitter:site" content="@|" />
			ifEmpty = {$plugin.tx_csseo.social.twitter.creator}
			required = 1
		}

		### twitter:image ###
		40 = FILES
		40 {
			references {
				table = pages
				uid.data = page:uid
				fieldName = tx_csseo_tw_image
			}
			renderObj = TEXT
			renderObj {
				stdWrap.typolink {
					parameter.stdWrap {
						cObject = IMG_RESOURCE
						cObject.file {
							import.data = file:current:uid
							treatIdAsReference = 1
							height < plugin.tx_csseo.social.twitter.image.height
							width < plugin.tx_csseo.social.twitter.image.width
						}
					}
					returnLast = url
					forceAbsoluteUrl = 1
				}
				required = 1
				wrap = <meta property="twitter:image" content="|" />
			}
		}

		### default twitter:image ###
		45 < .40.renderObj
		45 {
			stdWrap.typolink.parameter.stdWrap.cObject.file.import.data = path:{$plugin.tx_csseo.social.twitter.defaultImage} // path:{$plugin.tx_csseo.social.defaultImage}
			stdWrap.if.isFalse.field = tx_csseo_tw_image
		}
	}
}