(function (_, angular) {
  var module = angular.module('civicase');

  module.service('DraftPdfActivityForm', DraftPdfActivityForm);

  /**
   * Draft PDF activity form service.
   *
   * @param {Function} checkIfDraftActivity the check if draft activity function.
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function DraftPdfActivityForm (checkIfDraftActivity, civicaseCrmUrl) {
    this.canChangeStatus = false;
    this.canHandleActivity = checkIfDraftPdfLetter;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * @param {object} activity an activity object.
     * @returns {boolean} true when the activity status is draft and the type is a PDF letter.
     */
    function checkIfDraftPdfLetter (activity) {
      return checkIfDraftActivity(activity, ['Print PDF Letter']);
    }

    /**
     * @param {object} activity an activity object.
     * @param {object} [optionsWithoutDefaults={action: 'add'}]
     *   a list of options to display the form.
     * @returns {string} the form URL for activities that are PDF letter drafts.
     */
    function getActivityFormUrl (activity, optionsWithoutDefaults) {
      var options = _.defaults({}, optionsWithoutDefaults, { action: 'add' });
      var targetContactId = _.first(activity.target_contact_id);

      // Draft PDF cannot be opened in Update mode as it still in draft, so
      // enforcing 'add' action
      options.action = options.action === 'update' ? 'add' : options.action;

      return civicaseCrmUrl('civicrm/activity/pdf/' + options.action, {
        action: options.action,
        caseid: activity.case_id,
        cid: targetContactId,
        context: 'standalone',
        draft_id: activity.id,
        id: activity.id,
        reset: '1'
      });
    }
  }
})(CRM._, angular);
