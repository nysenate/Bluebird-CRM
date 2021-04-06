(function (_, angular) {
  var module = angular.module('civicase');

  module.service('EmailActivityForm', EmailActivityForm);

  /**
   * Email activity form service.
   *
   * Sent emails can only be opened in view mode.
   *
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function EmailActivityForm (civicaseCrmUrl) {
    this.canChangeStatus = true;
    this.canHandleActivity = isCompletedEmailActivity;
    this.getActivityFormUrl = getEmailActivityFormUrl;

    /**
     * @param {object} activity an activity object.
     * @returns {boolean} true when the activity belongs to a sent email.
     */
    function isCompletedEmailActivity (activity) {
      return activity.type.toLowerCase() === 'email' &&
        activity.status_type === 'completed';
    }

    /**
     * @param {object} activity an activity object.
     * @returns {string} the form URL for activities that are sent emails.
     */
    function getEmailActivityFormUrl (activity) {
      var context = activity.case_id ? 'case' : 'activity';

      return civicaseCrmUrl('civicrm/activity', {
        action: 'view',
        id: activity.id,
        reset: 1,
        context: context
      });
    }
  }
})(CRM._, angular);
