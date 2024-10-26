(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('WorkflowListColumns', function () {
    var workflowListColumns = [];

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the workflow list columns.
     *
     * @returns {object[]} the list of workflows.
     */
    function $get () {
      var workflowListColumnsSorted = _.sortBy(
        workflowListColumns,
        'weight'
      );

      return workflowListColumnsSorted;
    }

    /**
     * Adds the given workflow list columns to the list.
     *
     * @param {ColumnsConfig[]} itemsConfig a list of workflow list columns configurations.
     */
    function addItems (itemsConfig) {
      workflowListColumns = workflowListColumns.concat(itemsConfig);
    }
  });
})(angular, CRM.$, CRM._);

/**
 * @typedef {object} ColumnsConfig
 * @property {string} label
 * @property {string} templateUrl
 * @property {boolean} onlyVisibleForInstance
 */
