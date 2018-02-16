### Google Tag Manager ###
page.jsInline.654.15 = TEXT
page.jsInline.654.15 {
	value = {$plugin.tx_csseo.tracking.googleTagManager}
	wrap (
        /* Google Tag Manager */
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','#');
		/* End Google Tag Manager */
	)
	wrap.splitChar = #
	required = 1
}