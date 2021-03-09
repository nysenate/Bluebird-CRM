(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('LinkCasesCaseAction', LinkCasesCaseAction);

  /**
   * Link Cases Case Action service
   *
   * @param {object} $q $q service
   * @param {object} ActivityType ActivityType
   */
  function LinkCasesCaseAction ($q, ActivityType) {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases cases
     * @param {object} action action
     * @param {Function} callbackFn callback function
     * @returns {Promise} promise which resolves to the path for the popup
     */
    this.doAction = function (cases, action, callbackFn) {
      var case1 = cases[0];
      var case2 = cases[1];
      var activityTypes = ActivityType.getAll(true);
      var link = {
        path: 'civicrm/case/activity',
        query: {
          action: 'add',
          reset: 1,
          cid: case1.client[0].contact_id,
          atype: _.findKey(activityTypes, { name: 'Link Cases' }),
          caseid: case1.id
        }
      };

      if (case2) {
        link.query.link_to_case_id = case2.id;
      }

      return $q.resolve(link);
    };
  }
})(angular, CRM.$, CRM._);
