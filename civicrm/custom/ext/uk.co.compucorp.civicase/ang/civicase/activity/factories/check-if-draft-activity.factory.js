(function (_, angular) {
  var module = angular.module('civicase');

  module.factory('checkIfDraftActivity', checkIfDraftActivityFactory);

  /**
   * Check if activity service.
   *
   * @param {object} ActivityType the activity type service.
   * @returns {Function} the service function.
   */
  function checkIfDraftActivityFactory (ActivityType) {
    return checkIfDraftActivity;

    /**
     * @param {object} activity an activity object.
     * @param {string[]} allowedActivityTypes a list of activity types.
     * @returns {boolean} true if the activity is a draft. If a list of allowed types is sent
     *   it will also check if the activity belongs to one of them.
     */
    function checkIfDraftActivity (activity, allowedActivityTypes) {
      var activityType = ActivityType.findById(activity.activity_type_id);
      var isAllowedActivityType = _.includes(allowedActivityTypes, activityType.name);
      var isDraft = activity.status_name === 'Draft';

      return allowedActivityTypes
        ? isAllowedActivityType && isDraft
        : isDraft;
    }
  }
})(CRM._, angular);
