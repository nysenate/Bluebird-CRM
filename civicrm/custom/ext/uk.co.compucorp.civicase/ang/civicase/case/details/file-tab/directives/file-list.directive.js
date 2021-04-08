(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseFileList', function () {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/details/file-tab/directives/file-list.directive.html'
    };
  });
})(angular, CRM.$, CRM._);
