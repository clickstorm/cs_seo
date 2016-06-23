CsSeoController.$inject = ['$scope', '$http', 'i18nService', 'previewTitleFactory'];

csSeoApp.controller('CsSeoController', CsSeoController);

function CsSeoController ($scope, $http, i18nService, previewTitleFactory) {
	// array for show whitespace before page title
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

	// initialize thie grid
	$scope.gridOptions = csSEO.gridOptions;

	// initialize values
	$scope.msg = {};

	$scope.editView = 0;
	$scope.prbMax = 100;
	$scope.prbMin = 0;

	$scope.pageTitle = '';
	$scope.pageTitleOnly = 0;
	$scope.pageDescription = '';


	// watchers
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
	};

	$scope.$watch('prbValue', function (newValue, oldValue, $scope) {
		updateProcessBar(newValue);
	});


	// grid watchers
	$scope.gridOptions.onRegisterApi = function(gridApi){
		//set gridApi on scope
		$scope.gridApi = gridApi;

		// expand all rows on init
		$scope.gridApi.grid.registerDataChangeCallback(function() {
			$scope.gridApi.treeBase.expandAllRows();
		});

		// begin cell edit
		gridApi.edit.on.beginCellEdit($scope,function(rowEntity, colDef) {
			if(colDef.max) {
				$scope.editView = 1;
				$scope.prbMax = colDef.max;
				$scope.prbMin = colDef.min;
				$scope.$apply();
			}
			$scope.currentValue = rowEntity[colDef.field];
			if($scope.wizardInit) {
				$scope.pageTitle = rowEntity.title;
				$scope.pageDescription = rowEntity.description;
				$scope.pageCsSeoTitle = rowEntity.tx_csseo_title;
				$scope.pageTitleOnly = rowEntity.tx_csseo_title_only;
				$scope.currentField = colDef.field;
				$scope.previewTitle = previewTitleFactory.getTitle($scope.pageTitle, $scope.pageCsSeoTitle, $scope.pageTitleOnly);
			}
		});

		// after cell edit
		gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
			$scope.editView = 0;
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
						$scope.msg.error = response;
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
}