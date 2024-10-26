(function (angular, $, _) {
  var module = angular.module('civicase');

  module.controller('civicaseSearchPageController', civicaseSearchPageController);

  /**
   * @param {object} $scope scope object
   */
  function civicaseSearchPageController ($scope) {
    $scope.ts = CRM.ts('civicase');
    $scope.selections = {};
    $scope.show = function (selectedFilters) {
      $scope.selections = selectedFilters;
    };
    $scope.$bindToRoute({
      expr: 'selections',
      param: 's',
      default: { status_id: ['Urgent'] }
    });
  }
})(angular, CRM.$, CRM._);
