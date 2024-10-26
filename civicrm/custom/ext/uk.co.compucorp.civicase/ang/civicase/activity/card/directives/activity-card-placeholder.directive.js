(function (angular) {
  var module = angular.module('civicase');

  module.directive('civicaseActivityCardPlaceholder', function () {
    return {
      templateUrl: '~/civicase/activity/card/directives/activity-card-placeholder.directive.html',
      restrict: 'E'
    };
  });
}(angular));
