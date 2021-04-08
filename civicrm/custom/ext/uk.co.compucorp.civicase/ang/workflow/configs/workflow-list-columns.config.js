(function (angular) {
  var module = angular.module('workflow');

  module.config(function (WorkflowListColumnsProvider) {
    var actionItems = [
      {
        label: ts('Title'),
        templateUrl: '~/workflow/columns/directives/workflow-list-column-title.html',
        weight: 1
      }, {
        label: ts('Description'),
        templateUrl: '~/workflow/columns/directives/workflow-list-column-description.html',
        weight: 10
      }, {
        label: ts('Enabled?'),
        templateUrl: '~/workflow/columns/directives/workflow-list-column-is-enabled.html',
        weight: 20
      }
    ];

    WorkflowListColumnsProvider.addItems(actionItems);
  });
})(angular);
