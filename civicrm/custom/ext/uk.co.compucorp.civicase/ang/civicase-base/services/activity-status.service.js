(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('ActivityStatus', ActivityStatus);

  /**
   * Activity Status Service
   */
  function ActivityStatus () {
    var allActivityStatuses = CRM['civicase-base'].activityStatuses;
    var activeActivityStatuses = _.chain(allActivityStatuses)
      .filter(function (activityStatus) {
        return activityStatus.is_active === '1';
      })
      .indexBy('value')
      .value();

    this.getAll = getAll;

    /**
     * Get all Activity statuses
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {Array} all activity statuses
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allActivityStatuses : activeActivityStatuses;

      return returnValue;
    }
  }
})(angular, CRM.$, CRM._, CRM);
