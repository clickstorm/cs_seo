### PIWIK ###

page.jsInline.654.20 = TEXT
page.jsInline.654.20 {
	value = {$plugin.tx_csseo.tracking.piwik}
	wrap (
		/* PIWIK */
		var _paq = _paq || [];
		_paq.push(['trackPageView']);
		_paq.push(['enableLinkTracking']);
		(function() {
		var u="//#/";
		_paq.push(['setTrackerUrl', u+'js/']);
		_paq.push(['setSiteId', {$plugin.tx_csseo.tracking.piwik.id}]);
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'js/'; s.parentNode.insertBefore(g,s);
		})();
		/* End PIWIK */
	)
	wrap.splitChar = #
	required = 1
}