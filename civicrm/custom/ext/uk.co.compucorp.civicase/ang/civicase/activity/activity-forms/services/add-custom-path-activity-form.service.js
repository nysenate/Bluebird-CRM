(function (_, angular, getCrmUrl) {
  var module = angular.module('civicase');

  module.service('AddCustomPathActivityForm', AddCustomPathActivityForm);

  /**
   * Add Custom Path Activity Form service.
   */
  function AddCustomPathActivityForm () {
    var ACTIVITY_TYPES_CUSTOM_PATHS = {
      Email: 'civicrm/activity/email/add',
      'Print PDF Letter': 'civicrm/activity/pdf/add'
    };

    this.canChangeStatus = true;
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * The service can handle the creation of activities with custom save paths.
     *
     * @param {object} activity an activity object.
     * @param {object} options form options.
     * @returns {boolean} true when the service can handle the given activity.
     */
    function canHandleActivity (activity, options) {
      var isAddAction = options && options.action === 'add';
      var hasCustomPath = !!ACTIVITY_TYPES_CUSTOM_PATHS[activity.type];

      return isAddAction && hasCustomPath;
    }

    /**
     * @param {object} activity an activity object.
     * @param {object} overridingOptions form options.
     * @returns {string} the URL for the form to add the given activity.
     */
    function getActivityFormUrl (activity, overridingOptions) {
      var path = ACTIVITY_TYPES_CUSTOM_PATHS[activity.type];
      var options = _.defaults({}, overridingOptions, {
        action: 'add',
        reset: 1,
        caseid: activity.case_id,
        atype: activity.activity_type_id,
        context: 'standalone'
      });

      return getCrmUrl(path, options);
    }
  }
})(CRM._, angular, CRM.url);
