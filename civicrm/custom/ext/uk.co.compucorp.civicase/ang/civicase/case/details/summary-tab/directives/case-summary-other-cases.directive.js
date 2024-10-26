(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseSummaryOtherCases', function () {
    return {
      restrict: 'E',
      templateUrl: '~/civicase/case/details/summary-tab/directives/case-summary-other-cases.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
