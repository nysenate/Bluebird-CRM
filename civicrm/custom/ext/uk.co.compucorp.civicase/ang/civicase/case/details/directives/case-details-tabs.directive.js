(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseDetailsTabs', function () {
    return {
      replace: true,
      restrict: 'E',
      templateUrl: '~/civicase/case/details/directives/case-details-tabs.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
