(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseDetailsHeader', function () {
    return {
      replace: true,
      restrict: 'E',
      templateUrl: '~/civicase/case/details/directives/case-details-header.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
