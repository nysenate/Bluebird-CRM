(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('WorkflowListFilters', function () {
    var workflowListFilters = [];

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the workflow list filters.
     *
     * @returns {object[]} the list of filters.
     */
    function $get () {
      return workflowListFilters;
    }

    /**
     * Adds the given workflow list filters to the list.
     *
     * @param {FiltersConfig[]} itemsConfig a list of workflow list filters configurations.
     */
    function addItems (itemsConfig) {
      workflowListFilters = workflowListFilters.concat(itemsConfig);
    }
  });
})(angular, CRM.$, CRM._);

/**
 * @typedef {object} FiltersConfig
 * @property {string} filterIdentifier
 * @property {string} defaultValue
 * @property {string} templateUrl
 * @property {boolean} onlyVisibleForInstance
 */
