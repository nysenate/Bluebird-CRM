(function (angular) {
  var module = angular.module('civicase');

  module.service('ViewActivityForm', ViewActivityForm);

  /**
   * View Activity Form service.
   *
   * @param {object} civicaseCrmUrl civicrm url service
   */
  function ViewActivityForm (civicaseCrmUrl) {
    this.canChangeStatus = true;
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * The service can handle any activity that has already been saved.
     *
     * @param {object} activity an activity object.
     * @returns {boolean} true when the service can handle the given activity.
     */
    function canHandleActivity (activity) {
      return !!activity.id;
    }

    /**
     * @param {object} activity an activity object.
     * @returns {string} the URL for the form to view the given activity.
     */
    function getActivityFormUrl (activity) {
      var context = activity.case_id ? 'case' : 'activity';

      return civicaseCrmUrl('civicrm/activity', {
        action: 'view',
        id: activity.id,
        reset: 1,
        context: context
      });
    }
  }
})(angular);
