(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('PrintCaseAction', PrintCaseAction);

  /**
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function PrintCaseAction (civicaseCrmUrl) {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases list of cases
     * @param {object} action action object
     * @param {Function} callbackFn callback function
     */
    this.doAction = function (cases, action, callbackFn) {
      var selectedCase = cases[0];
      var url = civicaseCrmUrl('civicrm/case/report/print', {
        all: 1,
        redact: 0,
        cid: selectedCase.client[0].contact_id,
        caseID: selectedCase.id
      });
      var win = window.open(url, '_blank');

      win.focus();
    };
  }
})(angular, CRM.$, CRM._);
