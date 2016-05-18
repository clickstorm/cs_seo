### SEO & Social Tags with an example extension ###
[globalVar = GP:tx_example_pi1|item > 0]

	### Overwrite title ###
	config.pageTitle.cObject = TEXT
	config.pageTitle.cObject {
		data = DB:tx_example_domain_model_item:{GP:tx_example_pi1|item}:title
		data.insertData = 1
	}
	config.titleTagFunction >

	### Overwrite description ###
	meta {
		description.data = DB:tx_example_domain_model_item:{GP:tx_example_pi1|item}:description
		description.insertData = 1
	}

	### Overwrite other meta ###
	page.headerData.654 {
		### @TODO hreflang for detail views ###
		20 >

		### Open graph ###
		30 {
			### og:title ###
			10.data = DB:tx_example_domain_model_item:{GP:tx_example_pi1|item}:header
			10.insertData = 1

			### og:description ###
			20.data = DB:tx_example_domain_model_item:{GP:tx_example_pi1|item}:description
			10.insertData = 1
		}

		### Twitter Cards ###
		40 >
	}

[end]