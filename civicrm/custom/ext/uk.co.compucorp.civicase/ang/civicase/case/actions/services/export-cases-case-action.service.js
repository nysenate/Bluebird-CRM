(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('ExportCasesCaseAction', ExportCasesCaseAction);

  /**
   * ExportCasesCaseAction service.
   *
   * @param {object} $q $q service
   */
  function ExportCasesCaseAction ($q) {
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
      var caseIds = _.collect(cases, 'id');
      var popupPath = {
        path: 'civicrm/export/standalone',
        query: {
          reset: 1,
          entity: 'Case',
          id: caseIds.join()
        }
      };

      return $q.resolve(popupPath);
    };
  }
})(angular, CRM.$, CRM._);
