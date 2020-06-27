(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('PrintCaseAction', PrintCaseAction);

  /**
   *
   */
  function PrintCaseAction () {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases
     * @param {object} action
     * @param {Function} callbackFn
     */
    this.doAction = function (cases, action, callbackFn) {
      var selectedCase = cases[0];
      var url = CRM.url('civicrm/case/report/print', {
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
