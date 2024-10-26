(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseSummaryRecentCommunications', function () {
    return {
      restrict: 'E',
      templateUrl: '~/civicase/case/details/summary-tab/directives/case-summary-recent-communications.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
