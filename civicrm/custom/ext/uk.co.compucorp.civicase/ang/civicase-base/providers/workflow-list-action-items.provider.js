(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('WorkflowListActionItems', function () {
    var workflowListActionItems = [];

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the workflow list action items.
     * The items are sorted by their weight.
     *
     * @returns {object[]} the list of workflows.
     */
    function $get () {
      var workflowListActionItemsSorted = _.sortBy(
        workflowListActionItems,
        'weight'
      );

      return workflowListActionItemsSorted;
    }

    /**
     * Adds the given workflow list action items to the list.
     *
     * @param {ActionItemConfig[]} itemsConfig a list of workflow list action items configurations.
     */
    function addItems (itemsConfig) {
      workflowListActionItems = workflowListActionItems.concat(itemsConfig);
    }
  });
})(angular, CRM.$, CRM._);

/**
 * @typedef {object} ActionItemConfig
 * @property {string} templateUrl
 * @property {boolean} onlyVisibleForInstance
 * @property {number} weight
 */
