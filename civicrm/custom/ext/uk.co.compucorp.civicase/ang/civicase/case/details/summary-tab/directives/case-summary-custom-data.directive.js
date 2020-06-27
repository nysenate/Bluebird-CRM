(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseSummaryCustomData', function () {
    return {
      replace: true,
      restrict: 'E',
      templateUrl: '~/civicase/case/details/summary-tab/directives/case-summary-custom-data.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
