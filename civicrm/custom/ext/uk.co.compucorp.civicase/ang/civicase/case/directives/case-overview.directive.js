(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseOverview', function () {
    return {
      restrict: 'EA',
      replace: true,
      templateUrl: '~/civicase/case/directives/case-overview.directive.html',
      controller: civicaseCaseOverviewController,
      scope: {
        caseFilter: '<',
        linkToManageCase: '='
      },
      link: civicaseCaseOverviewLink
    };

    /**
     * Link function for civicaseCaseOverview
     *
     * @param {object} $scope scope object
     * @param {object} element the directive element
     * @param {object} attrs attributes of the directive
     */
    function civicaseCaseOverviewLink ($scope, element, attrs) {
      (function init () {
        $scope.$watch('showBreakdown', recalculateScrollbarPosition);
      }());

      /**
       * Watchers for showBreakdown variable
       */
      function recalculateScrollbarPosition () {
        $scope.$emit('civicase::custom-scrollbar::recalculate');
      }
    }
  });

  module.controller('civicaseCaseOverviewController', civicaseCaseOverviewController);

  /**
   * Controller for civicaseCaseOverview.
   *
   * @param {object} $scope the controller's $scope object.
   * @param {object} civicaseCrmApi the crm api service reference.
   * @param {object} BrowserCache the browser cache service reference.
   * @param {object} CaseStatus the case status service reference.
   * @param {object} CaseType the case type service reference.
   * @param {object} CaseTypeCategory the case type category service reference.
   * @param {Function} getServiceForInstance get service for a specific instance
   * @param {string} currentCaseCategory current case category
   */
  function civicaseCaseOverviewController ($scope, civicaseCrmApi, BrowserCache,
    CaseStatus, CaseType, CaseTypeCategory, getServiceForInstance,
    currentCaseCategory) {
    var BROWSER_CACHE_IDENTIFIER = 'civicase.CaseOverview.hiddenCaseStatuses';
    var MAXIMUM_CASE_TYPES_TO_DISPLAY_BREAKDOWN = 1;
    var allCaseStatusNames = _.map(CaseStatus.getAll(true), 'name');
    var caseStatusesIndexedByName = _.indexBy(CaseStatus.getAll(true), 'name');

    $scope.caseStatuses = [];
    $scope.caseTypes = [];
    $scope.hiddenCaseStatuses = {};
    $scope.summaryData = [];
    $scope.pageObj = { total: 0, size: 10, num: 1 };
    $scope.totalCount = 0;

    $scope.areAllStatusesHidden = areAllStatusesHidden;
    $scope.getItemsForCaseType = CaseType.getItemsForCaseType;
    $scope.toggleBreakdownVisibility = toggleBreakdownVisibility;
    $scope.toggleStatusVisibility = toggleStatusVisibility;
    $scope.setPageTo = setPageTo;

    (function init () {
      $scope.$watch('caseFilter', caseFilterWatcher, true);
      loadHiddenCaseStatuses();
    }());

    /**
     * Checks if all statuses are hidden
     *
     * @returns {boolean} true when all statuses are hidden.
     */
    function areAllStatusesHidden () {
      return _.filter($scope.caseStatuses, function (status) {
        return !status.isHidden;
      }).length === 0;
    }

    /**
     * Watcher function for caseFilter
     *
     * @param {object} caseFilters parameters to use for filtering the stats data.
     */
    function caseFilterWatcher (caseFilters) {
      var caseStatusNames;
      $scope.pageObj = { total: 0, size: 10, num: 1 };

      getCaseTypes()
        .then(function () {
          caseStatusNames = getCaseStatusNamesBelongingToCaseTypes($scope.caseTypes);
          $scope.caseStatuses = getSortedCaseStatusesByName(caseStatusNames);
          $scope.showBreakdown = $scope.caseTypes.length <=
            MAXIMUM_CASE_TYPES_TO_DISPLAY_BREAKDOWN;
          loadStatsData(caseFilters);
          $scope.$emit('civicase::custom-scrollbar::recalculate');
        });
    }

    /**
     * Get Case Types based on filters
     *
     * @returns {Promise} promise
     */
    function getCaseTypes () {
      var categoryObject = CaseTypeCategory.findByName(currentCaseCategory);
      var instanceName = CaseTypeCategory.getCaseTypeCategoryInstance(categoryObject.value).name;
      var params = {};

      // extract the params starting with `case_type_id` from case filters
      // Which means those params are meant for CaseType api.
      _.each($scope.caseFilter, function (value, key) {
        if (_.startsWith(key, 'case_type_id.')) {
          params[key.substr('case_type_id.'.length)] = value;
        }
      });

      return getServiceForInstance(instanceName)
        .getWorkflowsListForCaseOverview(params, $scope.pageObj)
        .then(function (result) {
          $scope.totalCount = result.count;
          $scope.pageObj.total = Math.ceil(result.count / $scope.pageObj.size);

          $scope.caseTypes = result.values;
        });
    }

    /**
     * Given a list of case types, it will return a unique list of
     * case status names as defined for each one of the case types.
     *
     * Note: When a case type supports all statuses, it does not store any status
     * names under `definition.statuses`. If the statuses definition is empty we
     * must assume the case supports all statuses.
     *
     * @param {object[]} caseTypes a list of case type objects.
     * @returns {string[]} a list of case sttus names.
     */
    function getCaseStatusNamesBelongingToCaseTypes (caseTypes) {
      return _.chain(caseTypes)
        .map(function (caseType) {
          return caseType.definition.statuses || allCaseStatusNames;
        })
        .flatten()
        .unique()
        .value();
    }

    /**
     * @param {string[]} caseStatusNames a list of case status names.
     * @returns {object[]} the full case status details belonging to the
     *   given case status names.
     */
    function getSortedCaseStatusesByName (caseStatusNames) {
      return _.chain(caseStatusNames)
        .map(function (caseStatusName) {
          return caseStatusesIndexedByName[caseStatusName];
        })
        .sortBy('weight')
        .indexBy('value')
        .value();
    }

    /**
     * Loads from the browser cache the ids of the case status that have been
     * previously hidden and marks them as such.
     */
    function loadHiddenCaseStatuses () {
      var hiddenCaseStatusesIds = BrowserCache.get(BROWSER_CACHE_IDENTIFIER, []);
      $scope.hiddenCaseStatuses = {};

      _.forEach(hiddenCaseStatusesIds, function (caseStatusId) {
        $scope.hiddenCaseStatuses[caseStatusId] = true;
      });
    }

    /**
     * Loads Stats data
     *
     * @param {object} caseFilters parameters to use for filtering the stats data.
     */
    function loadStatsData (caseFilters) {
      var apiCalls = [];

      var params = angular.copy(caseFilters || {});
      // status id should not be added to getstats,
      // because case overview section shows all statuses
      delete params.status_id;

      apiCalls.push(['Case', 'getstats', params]);
      civicaseCrmApi(apiCalls).then(function (response) {
        $scope.summaryData = response[0].values;
      });
    }

    /**
     * Set Page Number
     *
     * @param {number} page new page number
     */
    function setPageTo (page) {
      $scope.pageObj.num = page;

      getCaseTypes();
    }

    /**
     * Stores in the browser cache the id values of the case statuses that have been
     * hidden.
     */
    function storeHiddenCaseStatuses () {
      var hiddenCaseStatusesIds = _.chain($scope.hiddenCaseStatuses)
        .pick(function (caseStatusIsHidden) {
          return caseStatusIsHidden;
        })
        .keys()
        .value();

      BrowserCache.set(BROWSER_CACHE_IDENTIFIER, hiddenCaseStatusesIds);
    }

    /**
     * Toggles the visibility of the breakdown dropdown
     */
    function toggleBreakdownVisibility () {
      $scope.showBreakdown = !$scope.showBreakdown;
    }

    /**
     * Toggle status visibility.
     *
     * @param {document#event:mousedown} $event the toggle DOM event.
     * @param {number} caseStatusId the id for the case status to hide or show.
     */
    function toggleStatusVisibility ($event, caseStatusId) {
      $scope.hiddenCaseStatuses[caseStatusId] = !$scope.hiddenCaseStatuses[caseStatusId];

      storeHiddenCaseStatuses();
      $event.stopPropagation();
    }
  }
})(angular, CRM.$, CRM._);
