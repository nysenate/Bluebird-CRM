(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('HasIdCaseTypeFilter', HasIdCaseTypeFilter);

  /**
   * HasId Case Type Filter service.
   */
  function HasIdCaseTypeFilter () {
    this.run = run;
    this.shouldRun = shouldRun;

    /**
     * @param {object} caseType case type to match.
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when the case type is equal to the given ID.
     */
    function run (caseType, caseTypeFilters) {
      return caseType.id === caseTypeFilters.id;
    }

    /**
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when filters include a single ID used for matching.
     */
    function shouldRun (caseTypeFilters) {
      return caseTypeFilters.id && !caseTypeFilters.id.IN;
    }
  }
})(CRM._, angular);
