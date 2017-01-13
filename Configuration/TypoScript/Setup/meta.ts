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
			robots = noindex,nofollow
			robots.if.isTrue.field = tx_csseo_no_index
		}

		### SEO & Social Meta ###
		headerData.654 = COA
		headerData.654 {
			### canonical ###
			10 =< lib.currentUrl
			10 {
				wrap = <link rel="canonical" href="|" />
				if.isFalse.field = tx_csseo_no_index
			}

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

						}
						doNotLinkIt = 1
					}

					# Don't show hreflang for not localized languages
					USERDEF1 = 1
					USERDEF1.doNotShowLink = 1
				}
			}

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
					renderObj = IMG_RESOURCE
					renderObj {
						file.import.data = file:current:publicUrl
						file.height < plugin.tx_csseo.social.twitter.image.height
						file.width  < plugin.tx_csseo.social.twitter.image.width
						stdWrap.dataWrap = <meta name="twitter:image" content="{TSFE:baseUrl}|" />
					}
				}

				### default og:image ###
				45 = IMG_RESOURCE
				45 {
					stdWrap.if.isFalse.field = tx_csseo_tw_image
					file.import.data = path:{$plugin.tx_csseo.social.twitter.defaultImage} // path:{$plugin.tx_csseo.social.defaultImage}
					file.height < plugin.tx_csseo.social.twitter.image.height
					file.width  < plugin.tx_csseo.social.twitter.image.width
					stdWrap.dataWrap = <meta property="twitter:image" content="{TSFE:baseUrl}|" />
				}
			}
		}
	}
[end]