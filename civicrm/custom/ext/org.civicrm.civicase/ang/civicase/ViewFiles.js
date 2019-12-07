(function(angular, $, _) {
  // "civicaseViewFiles" is a basic skeletal directive.
  // Example usage: <div civicase-view-files="{foo: 1, bar: 2}"></div>
  angular.module('civicase').directive('civicaseViewFiles', function() {
    return {
      restrict: 'AE',
      templateUrl: '~/civicase/ViewFiles.html',
      scope: {
        item: '=civicaseViewFiles'
      },
      link: function($scope, $el, $attr) {
        var ts = $scope.ts = CRM.ts('civicase');
        $scope.$watch('item', function(newValue){
          
        });
      }
    };
  });
})(angular, CRM.$, CRM._);
