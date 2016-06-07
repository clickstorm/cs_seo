var app = angular.module('app', ['ui.grid', 'ui.grid.resizeColumns', 'ui.grid.moveColumns', 'ui.grid.treeView', 'ui.grid.selection', 'ui.grid.edit', 'ui.bootstrap']);

app.controller('MainCtrl', ['$scope', '$http', function ($scope, $http) {
	$scope.gridOptions = csSEOGridOptions;

	$scope.msg = {};

	$scope.prbHidden = 1;

	$scope.gridOptions.onRegisterApi = function(gridApi){
		//set gridApi on scope
		$scope.gridApi = gridApi;

		gridApi.edit.on.beginCellEdit($scope,function(rowEntity, colDef) {
			$scope.prbHidden = 0;
		});

		gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
			$scope.prbHidden = 1;
			$scope.msg.lastCellEdited = 'edited row id:' + rowEntity.uid + ' Column:' + colDef.name + ' newValue:' + newValue + ' oldValue:' + oldValue;

			if(newValue != oldValue) {
				$http.post(TYPO3.settings.ajaxUrls['CsSeo::update'], {
					entry: rowEntity,
					field : colDef.name,
					value: newValue
				}).success(function(response){
					$scope.msg.lastCellEdited = 'saved: ' + rowEntity.uid + ' Column:' + colDef.name + ' newValue:' + newValue + ' oldValue:' + oldValue;
				});
			}

			$scope.$apply();
		});
	};
}]);