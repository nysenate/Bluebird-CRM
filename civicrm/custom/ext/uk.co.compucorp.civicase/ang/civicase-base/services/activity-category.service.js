(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('ActivityCategory', ActivityCategory);

  /**
   * Activity Category Service
   *
   * @param {Function} isTruthy service to check if value is truthy
   */
  function ActivityCategory (isTruthy) {
    var allActivityCategories = CRM['civicase-base'].activityCategories;
    var activeActivityCategories = _.chain(allActivityCategories)
      .filter(function (activityCategory) {
        return isTruthy(activityCategory.is_active);
      })
      .indexBy('value')
      .value();

    this.getAll = getAll;

    /**
     * Get all Activity categories
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {Array} all activity categories
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allActivityCategories : activeActivityCategories;

      return returnValue;
    }
  }
})(angular, CRM.$, CRM._, CRM);
