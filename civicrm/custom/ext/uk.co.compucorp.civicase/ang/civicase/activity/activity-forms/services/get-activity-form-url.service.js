(function (angular, getCrmUrl) {
  var module = angular.module('civicase');

  module.factory('getActivityFormUrl', function () {
    return getActivityFormUrl;

    /**
     * @param {object} activity an activity object.
     * @param {object} options a list of options to display the form.
     * @returns {string} the URL for the activity form that will be displayed.
     */
    function getActivityFormUrl (activity, options) {
      var urlPath = 'civicrm/activity';
      var urlParams = {
        action: options.action,
        id: activity.id,
        reset: 1
      };

      if (activity.case_id) {
        urlPath = 'civicrm/case/activity';
        urlParams.caseid = activity.case_id;
      }

      return getCrmUrl(urlPath, urlParams);
    }
  });
})(angular, CRM.url);
