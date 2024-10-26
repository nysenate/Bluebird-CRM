(function ($, _, angular) {
  var module = angular.module('workflow');

  module.directive('workflowList', function () {
    return {
      scope: {
        caseTypeCategory: '@'
      },
      controller: 'workflowListController',
      templateUrl: '~/workflow/directives/workflow-list.directive.html',
      restrict: 'E'
    };
  });

  module.controller('workflowListController', workflowListController);

  /**
   * @param {object} $scope scope object
   * @param {object} ts translation service
   * @param {object[]} WorkflowListColumns list of workflow list columns
   * @param {object[]} WorkflowListActionItems list of workflow list action items
   * @param {object} CaseTypeCategory case type catgory service
   * @param {object[]} WorkflowListFilters list of workflow filters
   * @param {Function} getServiceForInstance get service for a specific instance
   */
  function workflowListController ($scope, ts, WorkflowListColumns,
    WorkflowListActionItems, CaseTypeCategory, WorkflowListFilters,
    getServiceForInstance) {
    $scope.ts = ts;
    $scope.isLoading = false;
    $scope.workflows = [];
    $scope.pageObj = { total: 0, size: 25, num: 1 };
    $scope.totalCount = 0;
    $scope.actionItems = filterArrayForCurrentInstance(WorkflowListActionItems);
    $scope.tableColumns = filterArrayForCurrentInstance(WorkflowListColumns);
    $scope.filters = filterArrayForCurrentInstance(WorkflowListFilters);
    $scope.selectedFilters = {};
    $scope.refreshWorkflowsList = refreshWorkflowsList;
    $scope.redirectToWorkflowCreationScreen = redirectToWorkflowCreationScreen;
    $scope.setPageTo = setPageTo;

    (function init () {
      applyDefaultValueToFilters();
      refreshWorkflowsList();

      $scope.$on('workflow::list::refresh', function () {
        resetPagination();
        refreshWorkflowsList();
      });
    }());

    /**
     * Set Page Number
     *
     * @param {number} page new page number
     */
    function setPageTo (page) {
      $scope.pageObj.num = page;

      refreshWorkflowsList();
    }

    /**
     * Apply default value to filters
     */
    function applyDefaultValueToFilters () {
      _.each($scope.filters, function (filter) {
        if (filter.filterSubObject) {
          $scope.selectedFilters[filter.filterSubObject] = $scope.selectedFilters[filter.filterSubObject] || {};
          $scope.selectedFilters[filter.filterSubObject][filter.filterIdentifier] = filter.defaultValue;
        } else {
          $scope.selectedFilters[filter.filterIdentifier] = filter.defaultValue;
        }
      });
    }

    /**
     * Apply default value to filters
     */
    function redirectToWorkflowCreationScreen () {
      var categoryObject = CaseTypeCategory.findByName($scope.caseTypeCategory);
      var instanceName = CaseTypeCategory.getCaseTypeCategoryInstance(categoryObject.value).name;

      getServiceForInstance(instanceName)
        .redirectToWorkflowCreationScreen(categoryObject);
    }

    /**
     * Preapres visibility settings for the sent array
     *
     * @param {object[]} arrayList array list
     * @returns {object[]} list
     */
    function filterArrayForCurrentInstance (arrayList) {
      return _.filter(arrayList, function (arrayItem) {
        return !arrayItem.onlyVisibleForInstance ||
          CaseTypeCategory.isInstance(
            $scope.caseTypeCategory,
            arrayItem.onlyVisibleForInstance
          );
      });
    }

    /**
     * Refresh workflows list
     */
    function refreshWorkflowsList () {
      $scope.isLoading = true;

      getWorkflows($scope.caseTypeCategory)
        .then(function (workflows) {
          $scope.workflows = workflows;
        })
        .finally(function () {
          $scope.isLoading = false;
        });
    }

    /**
     * Reset Pagination
     */
    function resetPagination () {
      $scope.pageObj = { total: 0, size: 25, num: 1 };
    }

    /**
     * Get list of workflows for the sent case type category
     *
     * @param {string} caseTypeCategory case type category
     * @returns {Promise} list of workflows
     */
    function getWorkflows (caseTypeCategory) {
      var categoryObject = CaseTypeCategory.findByName(caseTypeCategory);
      var instanceName = CaseTypeCategory.getCaseTypeCategoryInstance(categoryObject.value).name;

      var filters = _.cloneDeep($scope.selectedFilters);
      filters.case_type_category = $scope.caseTypeCategory;

      return getServiceForInstance(instanceName)
        .getWorkflowsListForManageWorkflow(filters, $scope.pageObj)
        .then(function (result) {
          $scope.totalCount = result.count;
          $scope.pageObj.total = Math.ceil(result.count / $scope.pageObj.size);

          return result.values;
        });
    }
  }
})(CRM.$, CRM._, angular);
