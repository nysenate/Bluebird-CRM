(function(angular, $, _) {

  function FileFilterCtrl($scope) {
    var ts = $scope.ts = CRM.ts('civicase');
    $scope.fileCategoriesIT = CRM.civicase.fileCategories;
    $scope.activityCategories = CRM.civicase.activityCategories;
    $scope.customFilters = {
      grouping: ''
    };
    $scope.$watchCollection('customFilters', function() {
      if (!_.isEmpty($scope.customFilters.grouping)) {
        $scope.apiCtrl.params['activity_type_id.grouping'] = {'LIKE': '%' + $scope.customFilters.grouping + '%'};
      }
      else {
        delete $scope.apiCtrl.params['activity_type_id.grouping'];
      }
    });
  }

  angular.module('civicase').directive('civicaseFileFilter', function() {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/FileFilter.html',
      controller: FileFilterCtrl,
      scope: {
        apiCtrl: '=civicaseFileFilter'
      }
    };
  });

})(angular, CRM.$, CRM._);
