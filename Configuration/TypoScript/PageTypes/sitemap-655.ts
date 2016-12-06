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
				storagePid = 14
				detailPid = 17
				languageUids = 0,1
				categories = 1
				categoryField =
				categoryMMTable = sys_category_record_mm
				categoryMMTablenames = 1
				categoryMMField = categories
			}
		}
		additional {
			1 = http://www.example.org/sitemap.xml
		}
	}
}