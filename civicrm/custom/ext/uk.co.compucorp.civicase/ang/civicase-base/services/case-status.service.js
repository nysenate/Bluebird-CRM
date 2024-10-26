(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('CaseStatus', CaseStatus);

  /**
   * Case Status Service
   *
   * @param {Function} isTruthy service to check if value is truthy
   */
  function CaseStatus (isTruthy) {
    var allCaseStatuses = CRM['civicase-base'].caseStatuses;
    var activeCaseStatus = _.chain(allCaseStatuses)
      .filter(function (caseStatus) {
        return isTruthy(caseStatus.is_active);
      })
      .indexBy('value')
      .value();

    this.getAll = getAll;
    this.getLabelsForValues = getLabelsForValues;

    /**
     * Get all Case statuses
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {object[]} a list of all the case statuses.
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allCaseStatuses : activeCaseStatus;

      return returnValue;
    }

    /**
     * Returns the labels for the given case status values.
     *
     * @param {string[]} statusValues a list of case status values.
     * @returns {string[]} a list of case status labels.
     */
    function getLabelsForValues (statusValues) {
      return _.map(statusValues, function (statusValue) {
        var caseStatus = _.find(allCaseStatuses, function (caseStatus) {
          return caseStatus.value === statusValue;
        });

        return caseStatus.label;
      });
    }
  }
})(angular, CRM.$, CRM._, CRM);
