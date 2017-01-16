/**
 * Robots.txt PageType
 *
 * @author Markus Biberger, TechDivision GmbH
 * @author Marc Hirdes, clickstorm GmbH
 * @package cs_seo
 * @subpackage Configuration\TypoScript\PageTypes
 */

pageCsSeoRobotsTxt = PAGE
pageCsSeoRobotsTxt {
	typeNum = 656

	config {
		disableAllHeaderCode = 1
		additionalHeaders = Content-Type:text/plain;charset=utf-8
		xhtml_cleaning = 0
		admPanel = 0
		debug = 0
		index_enable = 0
		removeDefaultJS = 1
		removeDefaultCss = 1
		removePageCss = 1
		INTincScript_ext.pagerender = 1
		sourceopt.enabled = 0
	}

	10 = CONTENT
	10 {
		table = sys_domain
		select {
			where = domainName = ###currentDomain###
			markers {
				currentDomain.data = getIndpEnv:HTTP_HOST
			}
		}
		renderObj = TEXT
		renderObj {
			field = tx_csseo_robots_txt
		}
		stdWrap.ifEmpty.cObject =< plugin.tx_csseo.robots
	}
}