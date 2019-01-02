page.headerData.654 {
	### hreflang ###
	20 = HMENU
	20 {
		if {
			# the hreflang tag will be set if:
			# the TS Setting hreflang.enable = 1
			isTrue < plugin.tx_csseo.hreflang.enable
			# no canonical page is set on page settings
			# "no index" is not set on page settings
			# no "content from pid" is set on page settings
			isFalse.data = page:tx_csseo_canonical // page:tx_csseo_no_index // page:content_from_pid
			# if all this is empty - no field from above is set
			isFalse.ifEmpty = 1
			isFalse.ifEmpty.if {
				# if current sys_language_uid is equals page:_PAGES_OVERLAY_LANGUAGE - means no fallback-page is displayed
				# if page:_PAGES_OVERLAY_LANGUAGE is not given the current sys_language_uid is 0 - default language page
				value.data = TSFE:sys_language_uid
				equals.data = page:_PAGES_OVERLAY_LANGUAGE
				equals.ifEmpty = 0
				negate = 1
			}
			# hreflang only if canonical and current url are equal
			value.cObject < lib.currentUrl
			equals.data = getIndpEnv:TYPO3_REQUEST_URL
		}
		special = language
		special.value < plugin.tx_csseo.hreflang.ids

		1 = TMENU
		1 {
			# Set hreflang for not-active languages
			NO = 1
			NO {
				stdWrap.cObject = COA
				stdWrap.cObject {
					1 = LOAD_REGISTER
					1 {
						lParam.cObject = TEXT
						lParam.cObject {
							value < plugin.tx_csseo.hreflang.ids
							listNum {
								stdWrap.data = register:count_HMENU_MENUOBJ
								stdWrap.wrap = |-1
								splitChar = ,
							}
						}
						lLabel.cObject = TEXT
						lLabel.cObject {
							value < plugin.tx_csseo.hreflang.keys
							listNum {
								stdWrap.data = register:count_HMENU_MENUOBJ
								stdWrap.wrap = |-1
								splitChar = ,
							}
						}
					}

					10 = TEXT
					10.data = register:lLabel
					10.wrap = <link rel="alternate" hreflang="|"

					20 =< lib.currentUrl
					20 {
						typolink {
							parameter.data = page:uid
							additionalParams >
							additionalParams.data = register:lParam
							additionalParams.wrap = &L=|
						}
						noTrimWrap = | href="|" />|
					}

					### only if localized page will be indexed ###
					if.isFalse.field = tx_csseo_canonical // tx_csseo_no_index // content_from_pid
				}
				doNotLinkIt = 1
			}

			# Don't show hreflang for not localized languages
			USERDEF1 = 1
			USERDEF1.doNotShowLink = 1
		}
	}
}