(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.provider('CaseType', CaseTypeServiceProvider);

  /**
   * CaseType Service provider
   */
  function CaseTypeServiceProvider () {
    var caseTypes = CRM['civicase-base'].caseTypes;
    var DEFAULT_GET_ALL_OPTIONS = {
      includeInactive: false
    };

    this.$get = $get;
    this.getAll = getAll;
    this.getByCategory = getByCategory;
    this.getById = getById;
    this.getTitlesForNames = getTitlesForNames;

    /**
     * Returns an instance of the case type service.
     *
     * @param {object} DashboardCaseTypeItems the dashboard case type items.
     * @param {object} RelationshipType relationship type service.
     * @returns {object} the case type service.
     */
    function $get (DashboardCaseTypeItems, RelationshipType) {
      return {
        getAll: getAll,
        getByCategory: getByCategory,
        getById: getById,
        getItemsForCaseType: getItemsForCaseType,
        getTitlesForNames: getTitlesForNames,
        getAllRolesByCategoryID: getAllRolesByCategory
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

      /**
       * @param {string|number} caseTypeCategoryID case type category id
       * @returns {object[]} case roles for the given category id
       */
      function getAllRolesByCategory (caseTypeCategoryID) {
        var allCaseTypesForGivenCategory = getByCategory(caseTypeCategoryID);

        return _.chain(allCaseTypesForGivenCategory)
          .map('definition')
          .map('caseRoles')
          .flatten()
          .compact() // removes undefined values
          .uniq('name') // removes same role present in different case types
          .map(function (caseRole) {
            var relationshipType = RelationshipType.getByName(caseRole.name);

            return {
              id: relationshipType.id,
              name: caseRole.name
            };
          })
          .uniq('id') // removes different versions of the same role(A-B or B-A)
          .value();
      }
    }

    /**
     * @param {object} options configuration options to use for filtering
     *   the case types.
     * @returns {object[]} a list of case types.
     */
    function getAll (options) {
      options = _.defaults({}, options, DEFAULT_GET_ALL_OPTIONS);

      return options.includeInactive
        ? caseTypes
        : getAllActive();
    }

    /**
     * @returns {object[]} all active case types.
     */
    function getAllActive () {
      return _.pick(caseTypes, _.matches({ is_active: '1' }));
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
