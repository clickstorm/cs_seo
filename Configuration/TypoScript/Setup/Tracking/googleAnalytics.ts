### Google Analytics ###

page.jsInline.654.10 = TEXT
page.jsInline.654.10 {
	value = {$plugin.tx_csseo.tracking.googleAnalytics}
	wrap (
        /* Google Analytics Optout */
        var gaProperty = '#';
        var disableStr = 'ga-disable-' + gaProperty;
        if (document.cookie.indexOf(disableStr + '=true') > -1) {
            window[disableStr] = true;
        }

        function gaOptout() {
            document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
            window[disableStr] = true;
        }

         /* Google Analytics */
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', gaProperty, 'auto');
        ga('set', 'anonymizeIp', true);
        ga('send', 'pageview');
	    /* End Google Analytics */
	)
	wrap.splitChar = #
	required = 1
}

### Downloads with Google Analytics ###
page.includeJSFooter {
	654 = EXT:cs_seo/Resources/Public/JavaScript/jquery.cs_seo.ga.js
	654.if.isTrue < plugin.tx_csseo.tracking.googleAnalytics
}
