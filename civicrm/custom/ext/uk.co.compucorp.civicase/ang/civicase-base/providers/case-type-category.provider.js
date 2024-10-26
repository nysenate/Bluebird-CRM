(function (angular, $, _, CRM, civicaseBaseSettings) {
  var module = angular.module('civicase-base');

  module.provider('CaseTypeCategory', CaseTypeCategoryProvider);

  /**
   * CaseTypeCategory Service Provider
   */
  function CaseTypeCategoryProvider () {
    var caseCategoryInstanceMapping = civicaseBaseSettings.caseCategoryInstanceMapping;
    var caseCategoryInstanceType = civicaseBaseSettings.caseCategoryInstanceType;
    var allCaseTypeCategories = civicaseBaseSettings.caseTypeCategories;
    var caseTypeCategoriesWhereUserCanAccessActivities =
      civicaseBaseSettings.caseTypeCategoriesWhereUserCanAccessActivities;
    var activeCaseTypeCategories = _.chain(allCaseTypeCategories)
      .filter(function (caseTypeCategory) {
        return caseTypeCategory.is_active === '1';
      })
      .indexBy('value')
      .value();

    this.$get = $get;
    this.findByName = findByName;
    this.findAllByInstance = findAllByInstance;
    this.getCaseTypeCategoryInstance = getCaseTypeCategoryInstance;
    this.getAll = getAll;
    this.isInstance = isInstance;

    /**
     * Returns the case the category service.
     *
     * @returns {object} the case type service.
     */
    function $get () {
      return {
        getAll: getAll,
        findById: findById,
        findByName: findByName,
        findAllByInstance: findAllByInstance,
        getCaseTypeCategoryInstance: getCaseTypeCategoryInstance,
        getCategoriesWithAccessToActivity: getCategoriesWithAccessToActivity,
        isInstance: isInstance
      };
    }

    /**
     * Find all case type categories belonging to sent instance name
     *
     * @param {string} instanceName name of the instance
     * @returns {object[]} list of case type categories matching the sent instance
     */
    function findAllByInstance (instanceName) {
      return _.filter(getAll(), function (caseTypeCategory) {
        return isInstance(caseTypeCategory.name, instanceName);
      });
    }

    /**
     * Check if the sent case type category is part of the sent instance
     *
     * @param {string} caseTypeCategoryName case type category name
     * @param {string} instanceName instance name
     * @returns {boolean} if the sent case type category is part of the sent instance
     */
    function isInstance (caseTypeCategoryName, instanceName) {
      var caseTypeCategoryObject = findByName(caseTypeCategoryName);

      if (!caseTypeCategoryObject) {
        return;
      }

      var caseTypeCategory = _.find(caseCategoryInstanceMapping, function (instanceMap) {
        return instanceMap.category_id === caseTypeCategoryObject.value;
      });

      if (!caseTypeCategory) {
        return;
      }

      var instanceID = _.find(caseCategoryInstanceType, function (instance) {
        return instance.name === instanceName;
      }).value;

      return caseTypeCategory.instance_id === instanceID;
    }

    /**
     * Get instance object for the sent case type category value
     *
     * @param {string} caseTypeCategoryValue case type category value
     * @returns {boolean} if the sent case type category is part of the sent instance
     */
    function getCaseTypeCategoryInstance (caseTypeCategoryValue) {
      var instanceID = _.find(caseCategoryInstanceMapping, function (instanceMap) {
        return instanceMap.category_id === caseTypeCategoryValue;
      }).instance_id;

      return _.find(caseCategoryInstanceType, function (instance) {
        return instance.value === instanceID;
      });
    }

    /**
     * Returns all case type categories.
     *
     * @param {Array} includeInactive if disabled option values also should be returned
     * @returns {object[]} all the case type categories.
     */
    function getAll (includeInactive) {
      var returnValue = includeInactive ? allCaseTypeCategories : activeCaseTypeCategories;

      return returnValue;
    }

    /**
     * Get a list of Case type categories of which,
     * the logged in user can access activities.
     *
     * @returns {Array} list of case categories
     */
    function getCategoriesWithAccessToActivity () {
      return _.filter(allCaseTypeCategories, function (caseTypeCategory) {
        return caseTypeCategoriesWhereUserCanAccessActivities.indexOf(caseTypeCategory.name) !== -1;
      });
    }

    /**
     * Find case type category by name
     *
     * @param {string} caseTypeCategoryName case type category name
     * @returns {object} case type category object
     */
    function findByName (caseTypeCategoryName) {
      return _.find(allCaseTypeCategories, function (category) {
        return category.name.toLowerCase() === caseTypeCategoryName.toLowerCase();
      });
    }

    /**
     * Find case type category by id
     *
     * @param {string} caseTypeCategoryID case type category id
     * @returns {object} case type category object
     */
    function findById (caseTypeCategoryID) {
      return _.find(allCaseTypeCategories, function (category) {
        return parseInt(category.value) === parseInt(caseTypeCategoryID);
      });
    }
  }
})(angular, CRM.$, CRM._, CRM, CRM['civicase-base']);
