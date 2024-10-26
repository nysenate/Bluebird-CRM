(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseSummaryTasks', function () {
    return {
      restrict: 'E',
      templateUrl: '~/civicase/case/details/summary-tab/directives/case-summary-tasks.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
