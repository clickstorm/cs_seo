# URL used for canonical, hreflang, og:url and USERFUNC HeaderData.php
lib.currentUrl = TEXT
lib.currentUrl {
	typolink {
		parameter.data = page:tx_csseo_canonical // page:content_from_pid // page:uid
		forceAbsoluteUrl = 1
		returnLast = url
		# set L Param manually
		# if page:_PAGES_OVERLAY_LANGUAGE is set, use this language in case of content_fallback
		# if page:_PAGES_OVERLAY_LANGUAGE is not set, use 0 - because only the page with language 0 hasn't this variable
		additionalParams.required = 1
		additionalParams.data = page:_PAGES_OVERLAY_LANGUAGE
		additionalParams.ifEmpty = 0
		additionalParams.wrap = &L=|
		# exclude L Param an other - L Param is set manually (additionalParams)
		addQueryString = 1
		addQueryString.exclude = L,utm_medium,utm_source,utm_campaign,utm_content,tx_search_pi1[query],src,ref,gclid,cx,ie,cof,siteurl,zanpid,_ult
	}
}