(function (angular) {
  var module = angular.module('civicase-base');

  /**
   * Paging directive.
   *
   * Usage:
   *
   * <civicase-paging
   *   paging-data="{ page: 3, pageSize: 25, total: 100, isDisabled: false }"
   *   paging-action="myPageChangeHandle($page, $pageSize, $total)"
   * ></civicase-paging>
   */
  module.directive('civicasePaging', function () {
    return {
      templateUrl: '~/civicase-base/directives/paging.html',
      scope: {
        pagingData: '<',
        pagingAction: '&'
      }
    };
  });
})(angular);
