(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseDashboard', function () {
    return {
      restrict: 'E',
      controller: 'civicaseDashboardController',
      templateUrl: '~/civicase/dashboard/directives/dashboard.directive.html'
    };
  });

  module.controller('civicaseDashboardController', civicaseDashboardController);

  /**
   * Civicase Dashboard Controller.
   *
   * @param {object} $scope controller's scope.
   * @param {string} currentCaseCategory current case type category setting value.
   * @param {object[]} DashboardActionItems Dashboard action items list.
   * @param {Function} includeActivitiesForInvolvedContact Include Activities For Involved Cases civicase setting.
   * @param {Function} ts translate service reference.
   * @param {Function} getServiceForInstance get service for a specific instance
   * @param {object} CaseTypeCategory the case type category service reference.
   */
  function civicaseDashboardController ($scope, currentCaseCategory,
    DashboardActionItems, includeActivitiesForInvolvedContact, ts,
    getServiceForInstance, CaseTypeCategory) {
    var categoryObject = CaseTypeCategory.findByName(currentCaseCategory);
    var instanceName = CaseTypeCategory.getCaseTypeCategoryInstance(categoryObject.value).name;

    $scope.checkPerm = CRM.checkPerm;
    $scope.actionBarItems = DashboardActionItems;
    $scope.url = CRM.url;
    $scope.filters = {};
    $scope.activityFilters = getServiceForInstance(instanceName).getActivityFilters();

    (function init () {
      bindRouteParamsToScope();
      initWatchers();
      prepareCaseFilterOption();
      $scope.caseTypeCategoryName = getCaseTypeCategoryName();
      $scope.currentCaseCategory = currentCaseCategory;
      $scope.ts = ts;
    }());

    /**
     * Creates link to the filtered cases list
     *
     * @param {string} type the case type
     * @param {string} status the case's status.
     * @returns {string} link to the filtered list of cases
     */
    $scope.linkToManageCase = function (type, status) {
      var cf = { case_type_category: $scope.caseTypeCategoryName };
      var userContactId = [CRM.config.user_contact_id];

      if (type) {
        cf.case_type_id = [type];
      }

      if (status) {
        cf.status_id = [status];
      }

      if ($scope.filters.caseRelationshipType === 'is_case_manager') {
        cf.case_manager = userContactId;
      } else if ($scope.filters.caseRelationshipType === 'is_involved') {
        cf.contact_involved = userContactId;
      }

      return '#/case/list?' + $.param({ cf: JSON.stringify(cf) });
    };

    /**
     * Bind route paramaters to scope variables
     */
    function bindRouteParamsToScope () {
      $scope.$bindToRoute({ param: 'dtab', expr: 'activeTab', format: 'int', default: 0 });
      $scope.$bindToRoute({
        param: 'drel',
        expr: 'filters.caseRelationshipType',
        format: 'raw',
        default: 'is_involved'
      });
      $scope.$bindToRoute({
        param: 'case_type_category',
        expr: 'activityFilters.case_filter["case_type_id.case_type_category"]',
        format: 'raw',
        default: null
      });
    }

    /**
     * Gets the case type category label.
     *
     * @returns {string} the case type category label.
     */
    function getCaseTypeCategoryName () {
      return $scope.activityFilters.case_filter['case_type_id.case_type_category'];
    }

    /**
     * Watcher for caseRelationshipType.
     *
     * @param {string} newValue the new relationship value.
     */
    function caseRelationshipTypeWatcher (newValue) {
      newValue === 'is_case_manager'
        ? $scope.activityFilters.case_filter.case_manager = CRM.config.user_contact_id
        : delete ($scope.activityFilters.case_filter.case_manager);

      if (newValue === 'is_involved') {
        $scope.activityFilters.case_filter.contact_involved = { IN: [CRM.config.user_contact_id] };
        $scope.activityFilters.case_filter.has_activities_for_involved_contact =
          includeActivitiesForInvolvedContact ? 1 : 0;
      } else {
        delete ($scope.activityFilters.case_filter.contact_involved);
      }
    }

    /**
     * Initialise watchers
     */
    function initWatchers () {
      $scope.$watch('filters.caseRelationshipType', caseRelationshipTypeWatcher);
    }

    /**
     * Prepare case filter options for crmUiSelect
     */
    function prepareCaseFilterOption () {
      var options = [
        { text: ts('My Cases'), id: 'is_case_manager' },
        { text: ts('Cases I am involved in'), id: 'is_involved' }
      ];

      if (CRM.checkPerm('access all cases and activities')) {
        options.push({ text: ts('All Cases'), id: 'all' });
      }

      $scope.caseRelationshipOptions = options;
    }
  }
})(angular, CRM.$, CRM._);
