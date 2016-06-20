app.controller('MainCtrl', ['$scope', '$http', 'i18nService', 'previewTitleFactory', function ($scope, $http, i18nService, previewTitleFactory) {

	$scope.rangeArray = [1,2,3,4,5];

	// highlight some cells
	angular.forEach(csSEO.gridOptions.columnDefs, function(value, key) {
		csSEO.gridOptions.columnDefs[key].cellClass = function(grid, row, col, rowRenderIndex, colRenderIndex) {
			if(!(row.entity.doktype == "1" || row.entity.doktype == "6")) {
				return 'text-muted';
			}
		}
	});

	// lang
	if(csSEO.gridOptions.i18n.length > 0 && csSEO.gridOptions.i18n != 'default') {
		i18nService.setCurrentLang(csSEO.gridOptions.i18n);
	}

	$scope.gridOptions = csSEO.gridOptions;

	$scope.msg = {};

	$scope.prbHidden = 1;
	$scope.prbMax = 100;
	$scope.prbMin = 0;
	$scope.wizardHide = 1;

	$scope.pageTitle = '';
	$scope.pageTitleOnly = 0;
	$scope.pageDescription = '';

	$scope.$watch('currentValue', function (newValue, oldValue, $scope) {
		if(newValue !== undefined) {
			var characterCount = newValue.length;
			if($scope.currentField == 'tx_csseo_title' && $scope.pageTitleOnly == false) {
				characterCount += csSEO.previewSettings.siteTitle.length;
			}
			$scope.prbValue = characterCount;

			switch ($scope.currentField) {
				case 'description':
					$scope.pageDescription = newValue;
					break;
				case 'title':
					$scope.pageTitle = newValue;
					break;
				case 'tx_csseo_title':
					$scope.pageCsSeoTitle = newValue;
					break;
				case 'tx_csseo_title_only':
					$scope.pageTitleOnly = newValue;
					break;
			}
			if($scope.currentField != 'description') {
				$scope.previewTitle = previewTitleFactory.getTitle($scope.pageTitle, $scope.pageCsSeoTitle, $scope.pageTitleOnly);
			}
		}
	});

	var updateProcessBar = function(length) {

		if(length > 0  && length < $scope.prbMax) {
			if($scope.prbMin) {
				$scope.prbType =  (length > $scope.prbMin) ? 'success' : 'warning';
			} else {
				$scope.prbType = 'info';
			}
		} else {
			$scope.prbType = 'danger';
		}
	}

	$scope.$watch('prbValue', function (newValue, oldValue, $scope) {
		updateProcessBar(newValue);
	});


	$scope.gridOptions.onRegisterApi = function(gridApi){
		//set gridApi on scope
		$scope.gridApi = gridApi;

		$scope.gridApi.grid.registerDataChangeCallback(function() {
			$scope.gridApi.treeBase.expandAllRows();
		});


		gridApi.edit.on.beginCellEdit($scope,function(rowEntity, colDef) {
			if(colDef.max) {
				$scope.prbHidden = 0;
				$scope.prbMax = colDef.max;
				$scope.prbMin = colDef.min;
				$scope.$apply();
			}
			$scope.currentValue = rowEntity[colDef.field];
			if($scope.wizardInit) {
				$scope.wizardHide = 0;
				$scope.pageTitle = rowEntity.title;
				$scope.pageDescription = rowEntity.description;
				$scope.pageCsSeoTitle = rowEntity.tx_csseo_title;
				$scope.pageTitleOnly = rowEntity.tx_csseo_title_only;
				$scope.currentField = colDef.field;
				$scope.previewTitle = previewTitleFactory.getTitle($scope.pageTitle, $scope.pageCsSeoTitle, $scope.pageTitleOnly);
			}
		});

		gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
			$scope.prbHidden = 1;
			$scope.wizardHide = 1;
			$scope.msg.field = colDef.displayName;
			$scope.msg.value = newValue;

			if(newValue != oldValue) {
				$scope.msg.state = 'wait';
				$http.post(TYPO3.settings.ajaxUrls['CsSeo::update'], {
					entry: rowEntity,
					field : colDef.name,
					value: newValue
				}).success(function(response){
					if(response.length > 0) {
						$scope.msg.state = 'error';
					} else {
						$scope.msg.state = 'success';
					}
				});
			} else {
				$scope.msg.state = 'no-changes';
			}

			$scope.$apply();
		});
	};
}]);