page.headerData.654 {
	### Open graph ###
	30 = COA
	30 {
		### og:type ###
		5 = TEXT
		5.value = <meta property="og:type" content="website" />

		### og:title ###
		10 = TEXT
		10 {
			data = page:tx_csseo_og_title // page:title
			htmlSpecialChars = 1
			wrap = <meta property="og:title" content="|" />
		}

		### og:description ###
		20 = TEXT
		20 {
			data = page:tx_csseo_og_description // page:description
			htmlSpecialChars = 1
			wrap = <meta property="og:description" content="|" />
			required = 1
		}

		### og:url ###
		30 =< lib.currentUrl
		30 {
			typolink {
				additionalParams >
				addQueryString.exclude = cHash,utm_medium,utm_source,utm_campaign,utm_content,tx_search_pi1[query],src,ref,gclid,cx,ie,cof,siteurl,zanpid,_ult
			}
			wrap = <meta property="og:url" content="|" />
		}

		### og:site_name ###
		40 = TEXT
		40 {
			data = TSFE:tmpl|sitetitle
			htmlSpecialChars = 1
			wrap = <meta property="og:site_name" content="|" />
		}

		### og:image ###
		50 = FILES
		50 {
			references {
				table = pages
				uid.data = page:uid
				fieldName = tx_csseo_og_image
			}
			renderObj = IMG_RESOURCE
			renderObj {
				file.import.data = file:current:publicUrl
				file.height < plugin.tx_csseo.social.openGraph.image.height
				file.width < plugin.tx_csseo.social.openGraph.image.width
				stdWrap.dataWrap = <meta property="og:image" content="{TSFE:baseUrl}|" />
			}
		}

		### default og:image ###
		55 = IMG_RESOURCE
		55 {
			stdWrap.if.isFalse.field = tx_csseo_og_image
			file.import.data = path:{$plugin.tx_csseo.social.defaultImage}
			file.height < plugin.tx_csseo.social.openGraph.image.height
			file.width < plugin.tx_csseo.social.openGraph.image.width
			stdWrap.dataWrap = <meta property="og:image" content="{TSFE:baseUrl}|" />
		}
	}
}