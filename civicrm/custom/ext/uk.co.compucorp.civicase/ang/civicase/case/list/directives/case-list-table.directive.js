(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseListTable', function ($document, $timeout) {
    return {
      controller: 'CivicaseCaseListTableController',
      link: civicaseCaseListTableLink,
      templateUrl: '~/civicase/case/list/directives/case-list-table.directive.html'
    };

    /**
     * Case List table directive link function
     *
     * @param {object} scope scope
     */
    function civicaseCaseListTableLink (scope) {
      (function init () {
        scope.$watch('caseIsFocused', setCaseListHeight);
        scope.$watch('viewingCase', setCaseListHeight);
      }());

      /**
       * Set Case list height
       */
      function setCaseListHeight () {
        var caseList = $('.civicase__case-list');

        if (scope.caseIsFocused || !scope.viewingCase) {
          caseList.height('auto');
        } else {
          setFixedListHeight();
        }
      }

      /**
       * Set height for case list when case is not focused
       * and viewing the case details
       */
      function setFixedListHeight () {
        var caseList = $('.civicase__case-list');
        var crmPageTitle = $('[crm-page-title]');
        var crmPageTitleHeight = crmPageTitle.is(':visible') ? crmPageTitle.outerHeight(true) : 0;
        var caseListFilterPanel = $('.civicase__case-filter-panel__form');
        var offsetTop = caseList.offset().top -
          caseListFilterPanel.outerHeight() - crmPageTitleHeight;
        var height = 'calc(100vh - ' + offsetTop + 'px)';

        caseList.height(height);
      }
    }
  });

  module.controller('CivicaseCaseListTableController', function ($q, $route,
    $scope, $window, BulkActions, civicaseCrmApi, currentCaseCategory,
    crmThrottle, formatCase, CasesUtils, ts, ActivityCategory, ActivityType,
    CaseStatus) {
    var firstLoad = true;
    var allCases = [];

    $scope.activityCategories = ActivityCategory.getAll();
    $scope.activityTypes = ActivityType.getAll();
    $scope.page = { total: 0 };
    $scope.cases = [];
    $scope.caseStatuses = CaseStatus.getAll();
    $scope.CRM = CRM;
    $scope.isLoading = true;
    $scope.selectedCases = [];
    $scope.sort = { sortable: true };
    $scope.ts = ts;
    $scope.viewingCaseDetails = null;

    $scope.bulkAllowed = BulkActions.isAllowed();

    (function init () {
      bindRouteParamsToScope();
      // calculate after page size is calculated from Route Params;
      $scope.casePlaceholders = _.range($scope.page.size);
      initiateWatchers();
      initSubscribers();
    }());

    $scope.applyAdvSearch = applyAdvSearch;
    $scope.changeSortDir = changeSortDir;
    $scope.isSelection = isSelection;
    $scope.refresh = refresh;
    $scope.unfocusCase = unfocusCase;
    $scope.viewCase = viewCase;

    /**
     * Apply advanced search
     *
     * @param {object} newFilters new filters object
     */
    function applyAdvSearch (newFilters) {
      $scope.filters = newFilters;
      getCases();
    }

    /**
     * Change Sort Direction
     */
    function changeSortDir () {
      $scope.sort.dir = ($scope.sort.dir === 'ASC' ? 'DESC' : 'ASC');
    }

    /**
     * Checks if selection is active on based of
     * the passed params.
     *
     * @param {string} condition condition
     * @returns {boolean} if selection is active
     */
    function isSelection (condition) {
      if (!$scope.cases) {
        return false;
      }

      var count = $scope.selectedCases.length;

      if (condition === 'all') {
        return count === $scope.cases.length;
      } else if (condition === 'any') {
        return !!count;
      }

      return count === condition;
    }

    /**
     * Refresh the Case List View
     *
     * @param {Array} apiCalls api calls
     * @param {boolean} backgroundLoading - if loading animation should not be
     *   shown
     */
    function refresh (apiCalls, backgroundLoading) {
      backgroundLoading = backgroundLoading || false;
      $scope.isLoading = true && !backgroundLoading;
      apiCalls = apiCalls || [];
      apiCalls = apiCalls.concat(getCaseApiParams(angular.extend({}, $scope.filters, $scope.hiddenFilters), $scope.sort, $scope.page));

      civicaseCrmApi(apiCalls, true)
        .then(function (result) {
          $scope.cases = _.each(result[apiCalls.length - 2].values, formatCase);
          $scope.totalCount = result[apiCalls.length - 1];
          $scope.isLoading = false;
          deselectAllCases();
        });
    }

    /**
     * Unfocus a case. It hides the details section
     */
    function unfocusCase () {
      $scope.caseIsFocused = false;
    }

    /**
     * View sent case
     *
     * @param {number/string} id id of the case
     * @param {object} $event event object
     */
    function viewCase (id, $event) {
      var currentCase = _.findWhere($scope.cases, { id: id });

      if (!$scope.bulkAllowed || currentCase.lock) {
        return;
      }

      if (!$event || !$($event.target).is('a, a *, input, button')) {
        $scope.unfocusCase();
        removeExtraRouteParams();
        if ($scope.viewingCase === id) {
          $scope.viewingCase = null;
          $scope.viewingCaseDetails = null;
        } else {
          $scope.viewingCaseDetails = _.findWhere($scope.cases, { id: id });
          $scope.viewingCase = id;
          $scope.viewingCaseTab = 'Summary';
        }
      }
      setPageTitle();
      $($window).scrollTop(0); // Scrolls the window to top once new data loads
    }

    /**
     * Remove route params added by individual tabs
     */
    function removeExtraRouteParams () {
      var allowedRouteParams = ['caseId', 'cf', 'all_statuses'];

      $route.current.params = _.pick($route.current.params, function (value, key) {
        return allowedRouteParams.indexOf(key) !== -1;
      });
    }

    /**
     * Binds all route parameters to scope
     */
    function bindRouteParamsToScope () {
      $scope.$bindToRoute({ expr: 'sort.field', param: 'sf', format: 'raw', default: 'contact_id.sort_name' });
      $scope.$bindToRoute({ expr: 'sort.dir', param: 'sd', format: 'raw', default: 'ASC' });
      $scope.$bindToRoute({ expr: 'caseIsFocused', param: 'focus', format: 'bool', default: false });
      $scope.$bindToRoute({ expr: 'viewingCase', param: 'caseId', format: 'raw' });
      $scope.$bindToRoute({ expr: 'viewingCaseTab', param: 'tab', format: 'raw', default: 'Summary' });
      $scope.$bindToRoute({ expr: 'page.size', param: 'cps', format: 'int', default: 15 });
      $scope.$bindToRoute({ expr: 'page.num', param: 'cpn', format: 'int', default: 1 });
    }

    /**
     * Bulk Selection Event Listener
     *
     * @param {object} event event
     * @param {string} condition condition
     */
    function bulkSelectionsListener (event, condition) {
      if (condition === 'none') {
        deselectAllCases();
      } else if (condition === 'visible') {
        selectDisplayedCases();
      } else if (condition === 'all') {
        selectEveryCase();
      }
    }

    /**
     * Bulk selection checkbox toggle Event Listener
     *
     * @param {object} event event
     * @param {object} data case object
     */
    function bulkSelectionCheckboxClickedListener (event, data) {
      if (data.selected) {
        $scope.selectedCases.push(data);
      } else {
        _.remove($scope.selectedCases, {
          id: data.id
        });
      }
    }

    /**
     * Case Watcher - Updates the checkbox if a case is selected
     *
     * @param {Array} cases cases
     */
    function casesWatcher (cases) {
      // if case is in selectedCases array update the UI model (checkbox)
      _.each(cases, function (item, index) {
        var isCurrentCaseInSelectedCases = _.find($scope.selectedCases, {
          id: item.id
        });
        if (isCurrentCaseInSelectedCases) {
          $scope.cases[index].selected = true;
        }
      });
    }

    /**
     * Deselection of all cases
     *
     * Updates the visible cases and other cases are updated on FE
     * by cases object watcher see `casesWatcher` function
     */
    function deselectAllCases () {
      _.each($scope.cases, function (item, index) {
        $scope.cases[index].selected = false;
      });
      $scope.selectedCases = [];
    }

    /**
     * Get all cases
     */
    function getCases () {
      allCases = [];
      $scope.isLoading = true;
      setPageTitle();

      crmThrottle(makeApiCallToLoadCases)
        .then(function (result) {
          var cases = _.each(result[0].values, formatCase);

          CasesUtils.fetchMoreContactsInformation(cases);

          if ($scope.viewingCase) {
            if ($scope.viewingCaseDetails) {
              var currentCase = _.findWhere(cases, { id: $scope.viewingCase });

              if (currentCase) {
                _.assign(currentCase, $scope.viewingCaseDetails);
              }
            } else {
              $scope.viewingCaseDetails = _.findWhere(cases, { id: $scope.viewingCase });
              $scope.caseNotFound = !$scope.viewingCaseDetails;
            }
          }

          if (typeof result[2] !== 'undefined') {
            $scope.headers = result[2].values;
          }

          $scope.cases = cases;

          if (result[0].page) {
            $scope.page.num = result[0].page;
          }

          $scope.totalCount = result[1];
          $scope.page.total = Math.ceil(result[1] / $scope.page.size);
          setPageTitle();
          firstLoad = $scope.isLoading = false;

          $($window).scrollTop(0); // Scrolls the window to top once new data loads
        });
    }

    /**
     * Get patameneters to load cases
     *
     * @param {object} filters filters
     * @param {object} sort sort
     * @param {object} page page
     *
     * @returns {Array} api params
     */
    function getCaseApiParams (filters, sort, page) {
      var returnCaseParams = {
        sequential: 1,
        return: [
          'subject', 'case_type_id', 'status_id', 'is_deleted', 'start_date',
          'modified_date', 'contacts', 'activity_summary', 'category_count',
          'tag_id.name', 'tag_id.color', 'tag_id.description',
          'case_type_id.case_type_category', 'case_type_id.is_active'
        ],
        options: {
          sort: sort.field + ' ' + sort.dir,
          limit: page.size,
          offset: page.size * (page.num - 1)
        }
      };

      // Keep things consistent and add a secondary sort on client name and a tertiary sort on case id
      if (sort.field !== 'id' && sort.field !== 'contact_id.sort_name') {
        returnCaseParams.options.sort += ', contact_id.sort_name';
      }
      if (sort.field !== 'id') {
        returnCaseParams.options.sort += ', id';
      }
      var params = {
        'case_type_id.is_active': 1,
        'case_type_id.case_type_category': currentCaseCategory
      };
      _.each(filters, function (val, filter) {
        if (val || typeof val === 'boolean') {
          if (filter === 'case_type_category') {
            params['case_type_id.case_type_category'] = val;
          } else if (filter === 'case_type_id.is_active') {
            params[filter] = val;
          } else if (typeof val === 'number' || typeof val === 'boolean') {
            params[filter] = val;
          } else if (typeof val === 'object' && !$.isArray(val)) {
            params[filter] = val;
          } else if (val.length) {
            params[filter] = $.isArray(val) ? { IN: val } : { LIKE: '%' + val + '%' };
          }
        }
      });
      // Filter out deleted contacts
      if (!params.contact_involved) {
        params.contact_is_deleted = 0;
      }
      // If no status specified, default to all open cases
      if (!params.status_id && !params.id && !params.showCasesFromAllStatuses) {
        params['status_id.grouping'] = 'Opened';
      }
      // Default to not deleted
      if (!params.is_deleted && !params.id) {
        params.is_deleted = 0;
      }

      return [
        ['Case', 'getcaselist', $.extend(true, returnCaseParams, params)],
        ['Case', 'getdetailscount', params]
      ];
    }

    /**
     * Asynchronously get all cases for the bulk actions select all
     * functionality actions functionality
     *
     * @returns {Promise} promise
     */
    function getAllCasesforSelectAll () {
      if (allCases.length > 0) {
        return $q.resolve(allCases);
      }

      $scope.selectedCases = []; // Resets all selection.

      var params = getCaseApiParams(
        angular.extend({}, $scope.filters, $scope.hiddenFilters),
        $scope.sort,
        $scope.page
      );

      params = params.splice(0, 1);
      params[0][2].return = ['case_type_id', 'status_id', 'is_deleted', 'contacts'];
      params[0][2].options.limit = 0;

      return civicaseCrmApi(params)
        .then(function (res) {
          allCases = res[0].values;
        });
    }

    /**
     * Initialise watchers
     */
    function initiateWatchers () {
      $scope.$watchCollection('sort', updateCases);
      $scope.$watchCollection('page.num', function () {
        $('.civicase__case-list-panel').scrollTop(0); // Scrolls the caselist to top once new data loads
        updateCases.apply(null, arguments);
      });
      $scope.$watch('cases', casesWatcher, true);
    }

    /**
     * Initiate subscribers
     */
    function initSubscribers () {
      $scope.$on('civicase::bulk-actions::bulk-selections', bulkSelectionsListener);
      $scope.$on('civicase::bulk-actions::check-box-toggled', bulkSelectionCheckboxClickedListener);
      $scope.$on('civicase::case-search::filters-updated', function (event, filters) {
        $scope.applyAdvSearch(filters.selectedFilters);
      });
    }

    /**
     * Make Api call to load cases
     *
     * @returns {Promise} promise
     */
    function makeApiCallToLoadCases () {
      var params = getCaseApiParams(angular.extend({}, $scope.filters, $scope.hiddenFilters), $scope.sort, $scope.page);
      if (firstLoad && $scope.viewingCase) {
        params[0][2].options.page_of_record = $scope.viewingCase;
      }

      if (firstLoad) {
        params.push(['Case', 'getcaselistheaders']);
      }

      return civicaseCrmApi(params);
    }

    /**
     * Select All visible data.
     */
    function selectDisplayedCases () {
      var isCurrentCaseInSelectedCases;

      _.each($scope.cases, function (item, index) {
        $scope.cases[index].selected = true;
        isCurrentCaseInSelectedCases = _.find($scope.selectedCases, {
          id: item.id
        });
        if (!isCurrentCaseInSelectedCases) {
          $scope.selectedCases.push(item);
        }
      });
    }

    /**
     * Select all Cases
     */
    function selectEveryCase () {
      getAllCasesforSelectAll()
        .then(function () {
          $scope.selectedCases = [];
          $scope.selectedCases = _.each(allCases, formatCase);

          selectDisplayedCases();
        });
    }

    /**
     * Emits event for page title
     */
    function setPageTitle () {
      $scope.$emit('civicase::case-search::page-title-updated', getDisplayNameOfSelectedItem(), $scope.totalCount);
    }

    /**
     * Update Cases when watch parameters has changed
     *
     * @param {object} newValue new value
     * @param {object} oldValue old value
     */
    function updateCases (newValue, oldValue) {
      if (newValue !== oldValue) {
        getCases();
      }
    }

    /**
     * Returns the display name of a selected case
     *
     * @returns {string} display name to be used in page title
     */
    function getDisplayNameOfSelectedItem () {
      var viewingCase = $scope.viewingCase;
      var cases = $scope.cases;

      if (!viewingCase) {
        return;
      }

      var selectedCase = _.findWhere(cases, { id: viewingCase });

      if (!selectedCase) {
        return false;
      }

      return selectedCase.client[0].display_name + ' - ' + selectedCase.case_type;
    }
  });
})(angular, CRM.$, CRM._);
