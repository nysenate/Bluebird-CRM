(function (angular, getCrmUrl) {
  var module = angular.module('civicase');

  module.service('UpdateActivityForm', UpdateActivityForm);

  /**
   * Update Activity Form service.
   *
   * @param {Function} getActivityFormUrl The get activity form URL service.
   */
  function UpdateActivityForm (getActivityFormUrl) {
    this.canChangeStatus = true;
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getUpdateActivityFormUrl;

    /**
     * Only handles activity forms that will be updated.
     * It supports both stand-alone activities and case activities.
     *
     * @param {object} activity an activity object.
     * @param {object} [options] form options.
     * @returns {boolean} true when it can handle the activity and form options.
     */
    function canHandleActivity (activity, options) {
      return (options && options.action === 'update') || false;
    }

    /**
     * @param {object} activity an activity object.
     * @returns {string} the URL for the activity form that will be updated.
     */
    function getUpdateActivityFormUrl (activity) {
      return getActivityFormUrl(activity, { action: 'update' });
    }
  }
})(angular, CRM.url);
