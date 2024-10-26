(function (angular, colorContrast) {
  var module = angular.module('civicase');

  module.directive('civicaseTag', function () {
    return {
      restrict: 'AE',
      scope: {
        tag: '=civicaseTag'
      },
      controller: 'civicaseTagController',
      templateUrl: '~/civicase/shared/directives/tag.directive.html'
    };
  });

  module.controller('civicaseTagController', function ($scope) {
    if ($scope.tag['tag_id.color']) {
      $scope.textColour = colorContrast($scope.tag['tag_id.color']);
    }
  });
})(angular, CRM.utils.colorContrast);
