app.factory('DatabaseService', function($http) {
	var siteTitle = 'TypoDummy',
		separator = ' | ',
		siteTitleFirst = false,
		factory = {};

	factory.update = function(rowEntity, colDef, newValue, oldValue) {
		if(newValue != oldValue) {
			$scope.msg.lastCellEdited = '... update: ' + colDef.name;
			$http.post(TYPO3.settings.ajaxUrls['CsSeo::update'], {
				entry: rowEntity,
				field : colDef.name,
				value: newValue
			}).success(function(response){
				if(response.length > 0) {
					$scope.msg.class = 'text-danger';
					$scope.msg.icon = 'fa-times-circle';
					$scope.msg.lastCellEdited = response;
				} else {
					$scope.msg.class = 'text-success';
					$scope.msg.icon = 'fa-check-circle';
					$scope.msg.lastCellEdited = '[' + colDef.displayName + '] ' + newValue;
				}

			});
		} else {
			$scope.msg.class = 'text-info';
			$scope.msg.icon = 'fa-info-circle';
			$scope.msg.lastCellEdited = 'no changes';
		}
	};

	return factory;
});