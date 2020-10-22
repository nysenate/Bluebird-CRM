(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('DashboardCaseTypeItems', function () {
    var dashboardCaseTypeItems = {};

    this.$get = $get;
    this.addItems = addItems;

    /**
     * Provides the case type buttons.
     *
     * @returns {object} a map of case type names and the buttons associated
     *   to them.
     */
    function $get () {
      return dashboardCaseTypeItems;
    }

    /**
     * Adds the given buttons to their corresponding case types. These buttons will be shown
     * in the list of case types on the dashboard.
     *
     * @typedef {{
     *  icon: string,
     *  url: string
     * }} ButtonConfig
     * @param {string} caseTypeName the name for the case type the buttons belong to.
     * @param {ButtonConfig[]} buttonsConfig a list of case type buttons configurations.
     */
    function addItems (caseTypeName, buttonsConfig) {
      var areButtonsDefined = !!dashboardCaseTypeItems[caseTypeName];

      if (!areButtonsDefined) {
        dashboardCaseTypeItems[caseTypeName] = [];
      }

      dashboardCaseTypeItems[caseTypeName] = dashboardCaseTypeItems[caseTypeName]
        .concat(buttonsConfig);
    }
  });
})(angular, CRM.$, CRM._);
