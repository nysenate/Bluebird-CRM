(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.provider('CaseDetailsTabs', CaseDetailsTabs);

  /**
   * Case Tabs provider.
   */
  function CaseDetailsTabs () {
    var caseTabsConfig = [];

    this.$get = $get;
    this.addTabs = addTabs;

    /**
     * A list of case tabs sorted by weight and including their case tab
     * services.
     *
     * @param {object} $injector the injector service reference.
     * @returns {object[]} a list of case tabs sorted by weight.
     */
    function $get ($injector) {
      var sortedCaseTabsConfig = _.sortBy(caseTabsConfig, 'weight');
      sortedCaseTabsConfig = getCaseTabsWithServices(sortedCaseTabsConfig);

      return sortedCaseTabsConfig;

      /**
       * Adds the case tab service to each given case tab.
       *
       * @param {object[]} caseTabs a list of case tabs.
       * @returns {object[]} a list of case tabs including their services.
       */
      function getCaseTabsWithServices (caseTabs) {
        return caseTabs.map(function (caseTab) {
          var caseTabService = getCaseTabServiceByName(caseTab.name);

          return _.extend({}, caseTab, {
            service: caseTabService
          });
        });
      }

      /**
       * Injects the case tab service using the case tab name and the
       * "CaseTab" suffix.
       *
       * @param {string} caseTabName the name of the case tab.
       * @returns {object|null} the case tab service.
       */
      function getCaseTabServiceByName (caseTabName) {
        try {
          return $injector.get(caseTabName + 'CaseTab');
        } catch (error) {
          return null;
        }
      }
    }

    /**
     * Setter for caseTabsConfig.
     * Adds a new caseTab config to the list of configs.
     *
     * @param {object[]} tabConfig the list of tabs to add.
     */
    function addTabs (tabConfig) {
      caseTabsConfig = caseTabsConfig.concat(tabConfig);
    }
  }
})(angular, CRM.$, CRM._);
