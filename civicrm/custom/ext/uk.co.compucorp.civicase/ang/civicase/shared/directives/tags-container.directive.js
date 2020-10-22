(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.directive('civicaseTagsContainer', function ($document) {
    return {
      restrict: 'E',
      replace: true,
      controller: 'civicaseTagsContainerController',
      templateUrl: '~/civicase/shared/directives/tags-container.directive.html',
      scope: {
        tags: '=',
        showEllipsisAfter: '@'
      }
    };
  });

  module.controller('civicaseTagsContainerController', civicaseTagsContainerController);

  /**
   * Controller function
   *
   * @param {object} $scope a reference to the scope object.
   */
  function civicaseTagsContainerController ($scope) {
    $scope.tagsArray = [];

    (function init () {
      $scope.$watch('tags', function () {
        $scope.tagsArray = $scope.tags
          ? _.values($scope.tags)
          : [];
      });
    }());
  }
})(angular, CRM.$, CRM._, CRM);
