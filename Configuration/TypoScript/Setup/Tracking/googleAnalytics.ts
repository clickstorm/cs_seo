### Google Analytics ###
page.headerData.654.100 = COA
page.headerData.654.100 {
	10 = TEXT
	10.value = {$plugin.tx_csseo.tracking.googleAnalytics}
	10.wrap (
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=#"></script>
	)
	10.wrap.splitChar = #
	10.required = 1

	20 = TEXT
	20.value = {$plugin.tx_csseo.tracking.googleAnalytics}
	20.wrap (
<!-- Global site tag (gtag.js) - Google Analytics -->
<script>
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
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', gaProperty, { 'anonymize_ip': true });
    /* End Google Analytics */
</script>
	)
	20.wrap.splitChar = #
	20.required = 1
}

### Downloads with Google Analytics ###
page.includeJSFooter {
	654 = EXT:cs_seo/Resources/Public/JavaScript/jquery.cs_seo.ga.js
	654.if.isTrue < plugin.tx_csseo.tracking.googleAnalytics
}
