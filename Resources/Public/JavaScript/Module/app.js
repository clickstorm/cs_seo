var app = angular.module('app', ['ui.grid', 'ui.grid.resizeColumns', 'ui.grid.moveColumns', 'ui.grid.treeView', 'ui.grid.edit', 'ui.grid.cellNav', 'ui.bootstrap']);

app.factory('PreviewTitleFactory', function() {
	var siteTitle = 'TypoDummy',
		separator = ' | ',
		siteTitleFirst = false,
		factory = {};

	factory.getTitle = function(pageTitle, pageCsSeoTitle, titleOnly) {
		var title = pageCsSeoTitle ? pageCsSeoTitle : pageTitle;
		if (titleOnly == 0) {
			if (siteTitleFirst) {
				title = siteTitle + separator + title;
			} else {
				title += separator + siteTitle;
			}
		}
		return title;
	};

	return factory;
});

app.controller('MainCtrl', ['$scope', '$http', '$sce', 'PreviewTitleFactory', function ($scope, $http, $sce, PreviewTitleFactory) {

	$scope.rangeArray = [1,2];

	// highlight some cells
	angular.forEach(csSEOGridOptions.columnDefs, function(value, key) {
		csSEOGridOptions.columnDefs[key].cellClass = function(grid, row, col, rowRenderIndex, colRenderIndex) {
			if(!(row.entity.doktype == "1" || row.entity.doktype == "6")) {
				return 'text-muted';
			}
		}
	});
	$scope.gridOptions = csSEOGridOptions;

	$scope.msg = {};

	$scope.prbHidden = 1;
	$scope.prbMax = 100;
	$scope.wizardHide = 1;

	$scope.pageTitle = '';
	$scope.pageTitleOnly = 0;
	$scope.pageDescription = '';

	$scope.$watch('currentValue', function (newValue, oldValue, $scope) {
		if(newValue !== undefined) {
			$scope.prbValue = newValue.length;

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
				$scope.previewTitle = PreviewTitleFactory.getTitle($scope.pageTitle, $scope.pageCsSeoTitle, $scope.pageTitleOnly);
			}
		}
	});

	$scope.$watch('prbValue', function (newValue, oldValue, $scope) {
		if(newValue) {
			$scope.prbType =  ((newValue / $scope.prbMax) > 0.8)  ? 'success' : 'warning';
		} else {
			$scope.prbType = 'danger';
		}
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
				$scope.$apply();
			}
			if($scope.wizardInit) {
				$scope.wizardHide = 0;
				$scope.pageTitle = rowEntity.title;
				$scope.pageDescription = rowEntity.description;
				$scope.pageCsSeoTitle = rowEntity.tx_csseo_title;
				$scope.pageTitleOnly = rowEntity.tx_csseo_title_only;
				$scope.currentField = colDef.field;
				$scope.currentValue = rowEntity[colDef.field];
				$scope.previewTitle = PreviewTitleFactory.getTitle($scope.pageTitle, $scope.pageCsSeoTitle, $scope.pageTitleOnly);
			}
		});

		gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
			$scope.prbHidden = 1;
			$scope.wizardHide = 1;
			$scope.msg.class = 'text-info';
			$scope.msg.icon = 'fa-info-circle';

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
						$scope.msg.lastCellEdited = colDef.displayName + ': ' + newValue;
					}

				});
			} else {
				$scope.msg.lastCellEdited = 'no changes';
			}

			$scope.$apply();
		});
	};
}]);