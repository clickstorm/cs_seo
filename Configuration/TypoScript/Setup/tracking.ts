### Session tracking ###
page {
	jsInline {
		654 = COA
		654 {
			### Google Analytics ###
			10 = TEXT
			10 {
				value = {$plugin.tx_csseo.tracking.googleAnalytics}
				wrap (
                // Google Analytics Opt-out
                var gaProperty = '#';
                var disableStr = 'ga-disable-' + gaProperty;
                if (document.cookie.indexOf(disableStr + '=true') > -1) {
                    window[disableStr] = true;
                }

                function gaOptout() {
                    document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
                    window[disableStr] = true;
                }

                // Google Analytics
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                ga('create', gaProperty, 'auto');
                ga('set', 'anonymizeIp', true);
                ga('send', 'pageview');
				)
				wrap.splitChar = #
				required = 1
			}

			### PIWIK ###
			20 = TEXT
			20 {
				value = {$plugin.tx_csseo.tracking.piwik}
				wrap (
             // Piwik
             var _paq = _paq || [];
              _paq.push(['trackPageView']);
              _paq.push(['enableLinkTracking']);
              (function() {
                var u="//#/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', {$plugin.tx_csseo.tracking.piwik.id}]);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
              })();
				)
				wrap.splitChar = #
				required = 1
			}
		}
	}

	### Downloads with Google Analytics ###
	includeJSFooter {
		654 = EXT:cs_seo/Resources/Public/JavaScript/jquery.cs_seo.ga.js
		654.if.isTrue < plugin.tx_csseo.googleAnalytics
	}
}

### Disable Tracking if Backend User detected ###
[globalVar = TSFE:beUserLogin > 0]
	page.jsInline.654 >
	page.includeJSFooter.654 >
[end]