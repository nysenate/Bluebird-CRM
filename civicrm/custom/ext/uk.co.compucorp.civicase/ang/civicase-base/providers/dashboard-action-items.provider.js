(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('DashboardActionItems', function () {
    var dashboardActionItems = [];

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the dashboard action items.
     * The items are sorted by their weight.
     *
     * @returns {object[]} the list of case types.
     */
    function $get () {
      var dashboardActionItemsSorted = _.sortBy(
        dashboardActionItems,
        'weight'
      );

      return dashboardActionItemsSorted;
    }

    /**
     * Adds the given dashboard action items to the list.
     *
     * @param {ButtonConfig[]} itemsConfig a list of dashboard action items configurations.
     */
    function addItems (itemsConfig) {
      dashboardActionItems = dashboardActionItems.concat(itemsConfig);
    }
  });
})(angular, CRM.$, CRM._);

/**
 * @typedef {object} ButtonConfig
 * @property {string} templateUrl
 * @property {number} weight
 */
