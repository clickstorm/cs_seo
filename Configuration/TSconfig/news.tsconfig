tx_csseo {
	# new index and table name of the model
	1 = tx_news_domain_model_news

	# getText. Returns news uid. If set, news meta tags are shown.
	1.enable = GP:tx_news_pi1|news

	# if the model already has fields like title etc. define them as fallback
	1.fallback {

		# cs_seo title field fallback = news title field
		title = title

		# cs_seo description field fallback = news description field
		description = description
	}

	# enable evaluation for news
	1.evaluation {
		# additional params to initialize the detail view, the pipe will be replaced by the uid
		getParams = &tx_news_pi1[controller]=News&tx_news_pi1[action]=detail&tx_news_pi1[news]=|

		# detail pid for the current records, only if set the table will be available
		detailPid = 
	}
}