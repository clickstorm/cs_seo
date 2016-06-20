app.factory('previewTitleFactory', function() {
	var factory = {};

	factory.getTitle = function(pageTitle, pageCsSeoTitle, titleOnly) {
		var title = pageCsSeoTitle ? pageCsSeoTitle : pageTitle;
		console.log(title);
		if (titleOnly == 0) {
			if (csSEO.previewSettings.pageTitleFirst) {
				title += csSEO.previewSettings.siteTitle;
			} else {
				title = csSEO.previewSettings.siteTitle + title;
			}
		}
		return title;
	};

	return factory;
});