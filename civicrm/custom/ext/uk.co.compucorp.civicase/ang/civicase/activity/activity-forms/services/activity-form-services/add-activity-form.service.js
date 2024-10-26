(function (_, angular) {
  var module = angular.module('civicase');

  module.service('AddActivityForm', AddActivityForm);

  /**
   * Add Activity Form service.
   *
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function AddActivityForm (civicaseCrmUrl) {
    this.canChangeStatus = true;
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * The service can handle any activity that needs to be added.
     *
     * @param {object} activity an activity object.
     * @param {object} options form options.
     * @returns {boolean} true when the service can handle the given activity.
     */
    function canHandleActivity (activity, options) {
      return options && options.action === 'add';
    }

    /**
     * @param {object} activity an activity object.
     * @param {object} overridingOptions form options.
     * @returns {string} the URL for the form to add the given activity.
     */
    function getActivityFormUrl (activity, overridingOptions) {
      var options = _.defaults({}, overridingOptions, {
        action: 'add',
        reset: 1,
        caseid: activity.case_id,
        atype: activity.activity_type_id
      });

      return civicaseCrmUrl('civicrm/case/activity', options);
    }
  }
})(CRM._, angular);
