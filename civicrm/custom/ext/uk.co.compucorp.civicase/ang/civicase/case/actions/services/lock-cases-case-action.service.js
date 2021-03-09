(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('LockCasesCaseAction', LockCasesCaseAction);

  /**
   * LockCasesCaseAction service.
   *
   * @param {object} $q $q service
   */
  function LockCasesCaseAction ($q) {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases list of cases
     * @param {object} action action to be performed
     * @param {Function} callbackFn the callback function
     *
     * @returns {Promise} promise which resolves to the path for the popup
     */
    this.doAction = function (cases, action, callbackFn) {
      return $q.resolve({
        path: 'civicrm/case/locked-contacts',
        query: {
          reset: 1,
          case_id: cases[0].id
        }
      });
    };
  }
})(angular, CRM.$, CRM._);
