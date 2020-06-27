(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('CaseTypeFilterer', CaseTypeFilterer);

  /**
   * CaseTypeFilterer
   *
   * @param {object} BelongsToCategoryCaseTypeFilter case type filter reference.
   * @param {object} CaseType case type service reference.
   * @param {object} HasIdCaseTypeFilter case type filter reference.
   * @param {object} IsIncludedInListOfIdsCaseTypeFilter case type filter reference.
   */
  function CaseTypeFilterer (BelongsToCategoryCaseTypeFilter, CaseType,
    HasIdCaseTypeFilter, IsIncludedInListOfIdsCaseTypeFilter) {
    var listOfFilters = [
      BelongsToCategoryCaseTypeFilter,
      IsIncludedInListOfIdsCaseTypeFilter,
      HasIdCaseTypeFilter
    ];

    this.filter = filter;

    /**
     * Returns a filtered list of case types. The case types are matched against
     * a list of filters. These filters are selected depending on the parameters
     * sent through `caseTypeFilters`.
     *
     * @param {object} caseTypeFilters parameters to use for filtering the case types.
     * @returns {object[]} a list of case types.
     */
    function filter (caseTypeFilters) {
      var caseTypes = _.values(CaseType.getAll());
      var listOfFiltersToRun = _.filter(listOfFilters, function (filter) {
        return filter.shouldRun(caseTypeFilters);
      });

      return _.filter(caseTypes, function (caseType) {
        return _.every(listOfFiltersToRun, function (filter) {
          return filter.run(caseType, caseTypeFilters);
        });
      });
    }
  }
})(CRM._, angular);
