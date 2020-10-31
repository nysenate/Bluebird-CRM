(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('ActivityType', ActivityType);

  /**
   * Activity Types Service
   */
  function ActivityType () {
    var allActivityTypes = CRM['civicase-base'].activityTypes;
    var activeActivityTypes = _.chain(allActivityTypes)
      .filter(function (activityType) {
        return activityType.is_active === '1';
      })
      .indexBy('value')
      .value();

    this.getAll = getAll;
    this.findById = findById;

    /**
     * Get all Activity types
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {Array} all activity types
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allActivityTypes : activeActivityTypes;

      return returnValue;
    }

    /**
     * Get Activity object by id
     *
     * @param {string/number} id activity id
     * @returns {object} activity object matching sent id
     */
    function findById (id) {
      return allActivityTypes[id];
    }
  }
})(angular, CRM.$, CRM._, CRM);
