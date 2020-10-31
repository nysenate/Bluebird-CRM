(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('ExportCasesCaseAction', ExportCasesCaseAction);

  function ExportCasesCaseAction () {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases
     * @param {Object} action
     * @param {Function} callbackFn
     */
    this.doAction = function (cases, action, callbackFn) {
      var caseIds = _.collect(cases, 'id');
      var popupPath = {
        path: 'civicrm/export/standalone',
        query: {
          reset: 1,
          entity: 'Case',
          id: caseIds.join()
        }
      };

      return popupPath;
    };
  }
})(angular, CRM.$, CRM._);
