(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('IsIncludedInListOfIdsCaseTypeFilter', IsIncludedInListOfIdsCaseTypeFilter);

  /**
   * IsIncludedInListOfIds Case Type Filter service.
   */
  function IsIncludedInListOfIdsCaseTypeFilter () {
    this.run = run;
    this.shouldRun = shouldRun;

    /**
     * @param {object} caseType case type to match.
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when the case type is included in the list of
     *   IDs defined in the filters.
     */
    function run (caseType, caseTypeFilters) {
      return _.includes(caseTypeFilters.id.IN, caseType.id);
    }

    /**
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when filters include a list of IDs.
     */
    function shouldRun (caseTypeFilters) {
      return caseTypeFilters.id && caseTypeFilters.id.IN;
    }
  }
})(CRM._, angular);
