app.factory('previewTitleFactory', function() {
	var siteTitle = '',
		siteTitleFirst = false,
		factory = {};

	factory.getTitle = function(pageTitle, pageCsSeoTitle, titleOnly) {
		var title = pageCsSeoTitle ? pageCsSeoTitle : pageTitle;
		if (titleOnly == 0) {
			if (siteTitleFirst) {
				title = siteTitle + title;
			} else {
				title += siteTitle;
			}
		}
		return title;
	};

	factory.init = function(scopeSiteTitle, scopeTitleFirst) {
		siteTitle = scopeSiteTitle;
		siteTitleFirst = scopeTitleFirst;
	};

	return factory;
});