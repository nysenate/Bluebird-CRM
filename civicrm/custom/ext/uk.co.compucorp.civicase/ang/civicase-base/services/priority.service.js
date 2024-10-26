(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('Priority', Priority);

  /**
   * Priority Service
   *
   * @param {Function} isTruthy service to check if value is truthy
   */
  function Priority (isTruthy) {
    var allPriorities = CRM['civicase-base'].priority;
    var activePriorities = _.chain(allPriorities)
      .filter(function (priority) {
        return isTruthy(priority.is_active);
      })
      .indexBy('value')
      .value();

    this.getAll = getAll;

    /**
     * Get all priorities
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {object[]} a list of all the priorities.
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allPriorities : activePriorities;

      return returnValue;
    }
  }
})(angular, CRM.$, CRM._, CRM);
