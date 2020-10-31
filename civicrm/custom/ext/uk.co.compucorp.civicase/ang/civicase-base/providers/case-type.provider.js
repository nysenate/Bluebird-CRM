(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.provider('CaseType', CaseTypeServiceProvider);

  /**
   * CaseType Service provider
   */
  function CaseTypeServiceProvider () {
    var caseTypes = CRM['civicase-base'].caseTypes;

    this.$get = $get;
    this.getAll = getAll;
    this.getByCategory = getByCategory;
    this.getById = getById;
    this.getTitlesForNames = getTitlesForNames;

    /**
     * Returns an instance of the case type service.
     *
     * @param {object} DashboardCaseTypeItems the dashboard case type items.
     * @returns {object} the case type service.
     */
    function $get (DashboardCaseTypeItems) {
      return {
        getAll: getAll,
        getByCategory: getByCategory,
        getById: getById,
        getItemsForCaseType: getItemsForCaseType,
        getTitlesForNames: getTitlesForNames
      };

      /**
       * Returns the Dashboard items for the given case type.
       *
       * @param {string} caseTypeName the name of the case type to get the buttons for.
       * @returns {object[]} a list of buttons.
       */
      function getItemsForCaseType (caseTypeName) {
        return DashboardCaseTypeItems[caseTypeName] || [];
      }
    }

    /**
     * @returns {object[]} a list of case types.
     */
    function getAll () {
      return caseTypes;
    }

    /**
     * Returns only the case types belonging to the given category.
     *
     * @param {number} categoryValue the case type category value.
     * @returns {object[]} a list of case types.
     */
    function getByCategory (categoryValue) {
      return _.filter(caseTypes, function (caseType) {
        return caseType.case_type_category === categoryValue;
      });
    }

    /**
     * @param {number} caseTypeId the id for the case type.
     * @returns {object} the case type for the given ID.
     */
    function getById (caseTypeId) {
      return caseTypes[caseTypeId];
    }

    /**
     * Returns a list of case type titles for the given names.
     *
     * @param {string[]} caseTypeNames the case type names.
     * @returns {string[]} a list of case type titles.
     */
    function getTitlesForNames (caseTypeNames) {
      return _.map(caseTypeNames, function (caseTypeName) {
        return _.findWhere(caseTypes, { name: caseTypeName }).title;
      });
    }
  }
})(angular, CRM.$, CRM._, CRM);
