(function (angular, $, _, CivicaseSettings) {
  var module = angular.module('civicase-base');

  module.service('CaseActions', CaseActions);

  /**
   * Case Actions Service
   */
  function CaseActions () {
    var allCaseActions = _.cloneDeep(CivicaseSettings.caseActions);

    this.getAll = getAll;
    this.findByActionName = findByActionName;

    /**
     * Get all Case actions
     *
     * @returns {object[]} all case actions
     */
    function getAll () {
      return allCaseActions;
    }

    /**
     * Finds a specific action object for the sent action name
     *
     * @param {string} actionName action name
     * @returns {object} action object
     */
    function findByActionName (actionName) {
      return _.find(allCaseActions, function (action) {
        return action.action === actionName;
      });
    }
  }
})(angular, CRM.$, CRM._, CRM.civicase);
