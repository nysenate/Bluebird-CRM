(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseBulkActionsMessage', function () {
    return {
      restrict: 'EA',
      controller: 'civicaseBulkActionsController',
      templateUrl: '~/civicase/bulk-action/directives/bulk-actions-message.directive.html',
      scope: {
        selectedItems: '=',
        isSelectAll: '=',
        isSelectAllAvailable: '=',
        totalCount: '=',
        showCheckboxes: '='
      }
    };
  });
})(angular, CRM.$, CRM._);
