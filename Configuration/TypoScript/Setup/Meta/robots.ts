page.headerData.654 {
	### robots ###
	15 = COA
	15 {
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
		wrap = <meta name="robots" content="|">
	}
}
