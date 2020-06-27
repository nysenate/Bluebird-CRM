(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseSummary', function () {
    return {
      replace: true,
      restrict: 'E',
      templateUrl: '~/civicase/case/details/summary-tab/directives/case-summary.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
