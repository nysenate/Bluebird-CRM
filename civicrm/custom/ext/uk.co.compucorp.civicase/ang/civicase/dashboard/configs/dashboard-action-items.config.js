(function (angular) {
  var module = angular.module('civicase');

  module.config(function (DashboardActionItemsProvider) {
    var actionItems = [
      {
        templateUrl: '~/civicase/dashboard/directives/add-case-dashboard-action-button.html',
        weight: 0
      }
    ];

    DashboardActionItemsProvider.addItems(actionItems);
  });
})(angular);
