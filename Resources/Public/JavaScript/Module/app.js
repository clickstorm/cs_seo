var app = angular.module('app', ['ui.grid', 'ui.grid.resizeColumns', 'ui.grid.moveColumns', 'ui.grid.treeView', 'ui.grid.edit', 'ui.grid.cellNav', 'ui.bootstrap']);

app.controller('MainCtrl', ['$scope', '$http', '$sce', function ($scope, $http, $sce) {
	$scope.gridOptions = csSEOGridOptions;

	$scope.msg = {};

	$scope.prbHidden = 1;
	$scope.prbMax = 100;
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
		});

		gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
			$scope.prbHidden = 1;
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