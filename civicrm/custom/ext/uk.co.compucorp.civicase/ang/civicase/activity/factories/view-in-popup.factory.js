(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('viewInPopup', function (ActivityForms) {
    /**
     * View activity in a popup
     *
     * @param {object} $event event
     * @param {object} activity activity to be viewed
     * @param {object} options configurations to use when opening the activity form.
     * @param {string} options.isReadOnly will display the form in view mode only.
     * @returns {object} jQuery object
     */
    function viewInPopup ($event, activity, options) {
      var action = (options && options.isReadOnly)
        ? 'view'
        : 'update';
      var formOptions = {
        action: action
      };
      var isClickingAButton = $event && $($event.target).is('a, a *, input, button, button *');
      var activityForm = ActivityForms.getActivityFormService(activity, formOptions);

      if (!activityForm || isClickingAButton) {
        return;
      }

      return CRM.loadForm(activityForm.getActivityFormUrl(activity, formOptions));
    }

    return viewInPopup;
  });
})(angular, CRM.$, CRM._, CRM);
