(function (angular, $, _, CivicaseSettings) {
  var module = angular.module('civicase-base');

  module.service('CaseActions', CaseActions);

  /**
   * Case Actions Service
   */
  function CaseActions () {
    var allCaseActions = _.cloneDeep(CivicaseSettings.caseActions);

    this.getAll = getAll;

    /**
     * Get all Case actions
     *
     * @returns {object[]} all case actions
     */
    function getAll () {
      return allCaseActions;
    }
  }
})(angular, CRM.$, CRM._, CRM.civicase);
