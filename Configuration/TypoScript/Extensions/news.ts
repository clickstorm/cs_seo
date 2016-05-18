### SEO & Social Tags with tx_news ###
[globalVar = GP:tx_news_pi1|news > 0]
	### Remove header data, this is set by news already ###
	page.headerData.654 >

	### overwrite title ###
	config.pageTitle.cObject = TEXT
	config.pageTitle.cObject {
		data = DB:tx_news_domain_model_news:{GP:tx_news_pi1|news}:title
		data.insertData = 1
	}
	config.titleTagFunction >
[end]