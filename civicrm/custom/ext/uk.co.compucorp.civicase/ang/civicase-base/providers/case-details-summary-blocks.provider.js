(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('CaseDetailsSummaryBlocks', function () {
    var caseDetailsSummaryBlocks = [];

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the case details summary blocks.
     * The items are sorted by their weight.
     *
     * @returns {object[]} the list of blocks.
     */
    function $get () {
      var caseDetailsSummaryBlocksSorted = _.sortBy(
        caseDetailsSummaryBlocks,
        'weight'
      );

      return caseDetailsSummaryBlocksSorted;
    }

    /**
     * Adds the given dashboard action items to the list.
     *
     * @param {BlockConfig[]} itemsConfig a list of dashboard action items configurations.
     */
    function addItems (itemsConfig) {
      caseDetailsSummaryBlocks = caseDetailsSummaryBlocks.concat(itemsConfig);
    }
  });
})(angular, CRM.$, CRM._);

/**
 * @typedef {object} BlockConfig
 * @property {string} templateUrl
 * @property {number} weight
 */
