(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('PrintMergeCaseAction', PrintMergeCaseAction);

  function PrintMergeCaseAction () {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases
     * @param {Object} action
     * @param {Function} callbackFn
     */
    this.doAction = function (cases, action, callbackFn) {
      var contactIds = [];
      var caseIds = [];

      _.each(cases, function (item) {
        caseIds.push(item.id);
        contactIds.push(item.client[0].contact_id);
      });

      return {
        path: 'civicrm/activity/pdf/add',
        query: {
          action: 'add',
          reset: 1,
          context: 'standalone',
          cid: contactIds.join(),
          caseid: caseIds.join()
        }
      };
    };
  }
})(angular, CRM.$, CRM._);
