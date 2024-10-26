(function (angular) {
  var module = angular.module('workflow');

  module.config(function (WorkflowListActionItemsProvider) {
    var actionItems = [
      {
        templateUrl: '~/workflow/action-links/directives/workflow-list-edit-action.html',
        weight: 0
      }, {
        templateUrl: '~/workflow/action-links/directives/workflow-list-duplicate-action.html',
        weight: 1
      }
    ];

    WorkflowListActionItemsProvider.addItems(actionItems);
  });
})(angular);
