# general plugin settings
plugin.tx_csseo {
	sitetitle = {$plugin.tx_csseo.sitetitle}
	hreflang {
		enable = {$plugin.tx_csseo.hreflang.enable}
		ids = {$plugin.tx_csseo.hreflang.ids}
		keys = {$plugin.tx_csseo.hreflang.keys}
		gp {

		}
	}
	social {
		defaultImage = {$plugin.tx_csseo.social.defaultImage}
		openGraph {
			image {
				height = {$plugin.tx_csseo.social.openGraph.image.height}
				width = {$plugin.tx_csseo.social.openGraph.image.width}
			}
			type = {$plugin.tx_csseo.social.openGraph.type}
		}
		twitter {
			creator = {$plugin.tx_csseo.social.twitter.creator}
			defaultImage = {$plugin.tx_csseo.social.twitter.defaultImage}
			image {
				height = {$plugin.tx_csseo.social.twitter.image.height}
				width = {$plugin.tx_csseo.social.twitter.image.width}
			}
		}
	}
	tracking {
		googleAnalytics = {$plugin.tx_csseo.tracking.googleAnalytics}
		piwik = {$plugin.tx_csseo.tracking.piwik}
		piwik.id = {$plugin.tx_csseo.tracking.piwik.id}
	}
	sitemap {
		view {
			layoutRootPaths {
				0 = EXT:cs_seo/Resources/Private/Layouts
				10 = {$plugin.tx_csseo.sitemap.view.layoutRootPath}
			}
			partialRootPaths {
				0 = EXT:cs_seo/Resources/Private/Partials
				10 = {$plugin.tx_csseo.sitemap.view.partialRootPath}
			}
			templateRootPaths {
				0 = EXT:cs_seo/Resources/Private/Templates/Sitemap/
				10 = {$plugin.tx_csseo.sitemap.view.templateRootPath}
			}
		}
		pages {
			rootPid = {$plugin.tx_csseo.sitemap.pages.rootPid}
			languageUids = {$plugin.tx_csseo.sitemap.pages.languageUids}
			doktypes = {$plugin.tx_csseo.sitemap.pages.doktypes}
		}
		additional {
			1 = {$plugin.tx_csseo.sitemap.additional}
		}
	}
	# default robots.txt content #
	robots = TEXT
	robots {
		data = getIndpEnv : TYPO3_REQUEST_HOST
		noTrimWrap = | ||
		wrap2 (
User-Agent: *
Allow: /

# folders
Disallow: /t3lib/
Disallow: /typo3/
Disallow: /typo3conf/
Allow: /typo3conf/ext/
Allow: /typo3temp/

# parameters
Disallow: /*?id=*               # non speaking URLs
Disallow: /*cHash               # no cHash
Disallow: /*tx_powermail_pi1    # no powermail thanks pages
Disallow: /*tx_form_formframework    # no forms

# sitemap
Sitemap: |/sitemap.xml
		)
	}
	structureddata {
		search {
			enable = {$plugin.tx_csseo.structureddata.search.enable}
			pid = {$plugin.tx_csseo.structureddata.search.pid}
			searchtermkey = {$plugin.tx_csseo.structureddata.search.searchtermkey}
		}
		breadcrumb {
			enable = {$plugin.tx_csseo.structureddata.breadcrumb.enable}
		}
	}
}
