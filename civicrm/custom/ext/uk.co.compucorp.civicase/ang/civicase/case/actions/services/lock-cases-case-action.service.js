(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('LockCasesCaseAction', LockCasesCaseAction);

  function LockCasesCaseAction () {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases
     * @param {Object} action
     * @param {Function} callbackFn
     */
    this.doAction = function (cases, action, callbackFn) {
      return {
        path: 'civicrm/case/locked-contacts',
        query: {
          reset: 1,
          case_id: cases[0].id
        }
      };
    };
  }
})(angular, CRM.$, CRM._);
