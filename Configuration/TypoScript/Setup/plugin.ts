# general plugin settings
plugin.tx_csseo {
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
}