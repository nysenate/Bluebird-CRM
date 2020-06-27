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
   * @param {Function} crmApi CRM API service reference.
   * @param {string} currentCaseCategory current case type category setting value.
   * @param {object[]} DashboardActionItems Dashboard action items list.
   * @param {Function} formatActivity Format Activity service reference.
   * @param {Function} $timeout timeout service reference.
   * @param {Function} ts translate service reference.
   */
  function civicaseDashboardController ($scope, crmApi, currentCaseCategory, DashboardActionItems,
    formatActivity, $timeout, ts) {
    $scope.checkPerm = CRM.checkPerm;
    $scope.actionBarItems = DashboardActionItems;
    $scope.url = CRM.url;
    $scope.filters = {};
    $scope.activityFilters = {
      case_filter: { 'case_type_id.is_active': 1, contact_is_deleted: 0 }
    };

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
      $scope.$bindToRoute({ param: 'drel', expr: 'filters.caseRelationshipType', format: 'raw', default: 'is_involved' });
      $scope.$bindToRoute({ param: 'case_type_category', expr: 'activityFilters.case_filter["case_type_id.case_type_category"]', format: 'raw', default: null });
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

      newValue === 'is_involved'
        ? $scope.activityFilters.case_filter.contact_involved = { IN: [CRM.config.user_contact_id] }
        : delete ($scope.activityFilters.case_filter.contact_involved);
    }

    /**
     * Initialise watchers
     */
    function initWatchers () {
      $scope.$on('civicase::dashboard-filters::updated', updateFilterParams);
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

    /**
     * Update Filter Parameters
     *
     * @param {object} event event
     * @param {object} data data sent from the broadcaster
     */
    function updateFilterParams (event, data) {
      _.extend($scope.activityFilters.case_filter, data);
    }
  }
})(angular, CRM.$, CRM._);
