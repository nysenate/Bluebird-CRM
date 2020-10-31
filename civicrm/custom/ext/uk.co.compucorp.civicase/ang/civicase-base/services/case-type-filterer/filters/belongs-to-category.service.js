(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('BelongsToCategoryCaseTypeFilter', BelongsToCategoryCaseTypeFilter);

  /**
   * BelongsToCategory Case Type Filter service.
   *
   * @param {object} CaseTypeCategory case type category service reference.
   */
  function BelongsToCategoryCaseTypeFilter (CaseTypeCategory) {
    this.run = run;
    this.shouldRun = shouldRun;

    /**
     * @param {object} caseType case type to match.
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when the case type belongs to the given category.
     */
    function run (caseType, caseTypeFilters) {
      var caseCategory = CaseTypeCategory
        .findByName(caseTypeFilters.case_type_category);

      return caseCategory && caseType.case_type_category === caseCategory.value;
    }

    /**
     * @param {object} caseTypeFilters list of parameters to use for matching.
     * @returns {boolean} true when filters include a case type category parameter.
     */
    function shouldRun (caseTypeFilters) {
      return !!caseTypeFilters.case_type_category;
    }
  }
})(CRM._, angular);
