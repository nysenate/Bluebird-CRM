(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('PrintMergeCaseAction', PrintMergeCaseAction);

  /**
   * PrintMergeCaseAction service.
   *
   * @param {object} $q $q service
   */
  function PrintMergeCaseAction ($q) {
    this.isActionAllowed = isActionAllowed;
    this.doAction = doAction;

    /**
     * Check if action is allowed.
     *
     * @param {object} action - action data.
     * @param {object} cases - cases.
     * @param {object} attributes - item attributes.
     *
     * @returns {boolean} - true if action is allowed, false otherwise.
     */
    function isActionAllowed (action, cases, attributes) {
      return attributes.mode === 'case-bulk-actions';
    }

    /**
     * Click event handler for the Action
     *
     * @param {Array} cases list of cases
     * @param {object} action action to be performed
     * @param {Function} callbackFn the callback function
     *
     * @returns {Promise} promise which resolves to the path for the popup
     */
    function doAction (cases, action, callbackFn) {
      var contactIds = [];
      var caseIds = [];

      _.each(cases, function (item) {
        caseIds.push(item.id);
        contactIds.push(item.client[0].contact_id);
      });

      return $q.resolve({
        path: 'civicrm/activity/pdf/add',
        query: {
          action: 'add',
          reset: 1,
          context: 'standalone',
          cid: contactIds.join(),
          caseid: caseIds.join()
        }
      });
    }
  }
})(angular, CRM.$, CRM._);
