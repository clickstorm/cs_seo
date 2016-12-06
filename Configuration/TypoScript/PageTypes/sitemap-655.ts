### Sietmap.xml ###
pageCsSeoSitemap = PAGE
pageCsSeoSitemap {
	typeNum = 655

	config {
		disableAllHeaderCode = 1
		xhtml_cleaning = 0
		admPanel = 0
		debug = 0
		removeDefaultJS = 1
		removeDefaultCss = 1
		removePageCss = 1
		additionalHeaders = Content-Type:application/xml;charset=utf-8
	}

	10 = USER
	10.userFunc = Clickstorm\CsSeo\UserFunc\Sitemap->main

	10.settings {
		pages {
			rootPid = 1
			languageUids = 0,1
		}
		extensions {
			news {
				table = tx_news_domain_model_news
				getParameter = tx_news_pi1[news]
				categories =
				storagePid =
				detailPid = 17
				categories =
				categoryTable =
				languageUids = 0,1
			}
		}
		additional {
			1 = http://www.example.org/sitemap.xml
		}
	}
}