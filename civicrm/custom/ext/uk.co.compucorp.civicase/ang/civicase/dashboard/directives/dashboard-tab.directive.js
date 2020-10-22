(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseDashboardTab', function () {
    return {
      restrict: 'E',
      controller: 'dashboardTabController',
      templateUrl: '~/civicase/dashboard/directives/dashboard-tab.directive.html'
    };
  });

  module.controller('dashboardTabController', dashboardTabController);

  /**
   * Dashboard Tab Controller
   *
   * @param {object} $location location service
   * @param {object} $rootScope rootScope object
   * @param {object} $route route object
   * @param {object} $sce sce service
   * @param {object} $scope scope object
   * @param {object} ContactsCache contacts cache service
   * @param {object} crmApi crm api service
   * @param {object} formatCase format case service
   * @param {object} formatActivity format activity service
   * @param {object} ts ts
   * @param {object} ActivityStatusType activity status type service
   */
  function dashboardTabController ($location, $rootScope, $route, $sce, $scope,
    ContactsCache, crmApi, formatCase, formatActivity, ts, ActivityStatusType) {
    var ACTIVITIES_QUERY_PARAMS_DEFAULTS = {
      contact_id: 'user_contact_id',
      is_current_revision: 1,
      is_deleted: 0,
      is_test: 0,
      'activity_type_id.grouping': { 'NOT LIKE': '%milestone%' },
      activity_type_id: { '!=': 'Bulk Email' },
      status_id: { IN: ActivityStatusType.getAll().incomplete },
      options: { sort: 'is_overdue DESC, activity_date_time ASC' },
      return: [
        'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
        'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
        'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
        'is_overdue', 'case_id', 'priority_id', 'case_id.case_type_id', 'case_id.status_id',
        'case_id.contacts'
      ]
    };
    var CASES_QUERY_PARAMS_DEFAULTS = {
      'status_id.grouping': 'Opened',
      options: { sort: 'start_date DESC' },
      is_deleted: 0
    };
    var MILESTONES_QUERY_PARAMS_DEFAULTS = {
      contact_id: 'user_contact_id',
      is_current_revision: 1,
      is_deleted: 0,
      is_test: 0,
      'activity_type_id.grouping': { LIKE: '%milestone%' },
      status_id: { IN: ActivityStatusType.getAll().incomplete },
      options: { sort: 'is_overdue DESC, activity_date_time ASC' },
      return: [
        'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
        'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
        'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
        'is_overdue', 'case_id', 'priority_id', 'case_id.case_type_id', 'case_id.status_id',
        'case_id.contacts'
      ]
    };

    var defaultsMap = {
      activities: ACTIVITIES_QUERY_PARAMS_DEFAULTS,
      cases: CASES_QUERY_PARAMS_DEFAULTS,
      milestones: MILESTONES_QUERY_PARAMS_DEFAULTS
    };

    $scope.ts = ts;
    $scope.calendarCaseParams = null;
    $scope.activitiesPanel = {
      name: 'activities',
      config: {},
      query: {
        entity: 'Activity',
        action: 'getcontactactivities',
        countAction: 'getcontactactivitiescount',
        params: getQueryParams('activities')
      },
      custom: {
        itemName: 'activities',
        involvementFilter: { '@involvingContact': 'myActivities' },
        cardRefresh: activityCardRefreshActivities
      },
      handlers: {
        range: _.curry(rangeHandler)('activity_date_time')('YYYY-MM-DD HH:mm:ss')(false),
        results: _.curry(resultsHandler)(formatActivity)('case_id.contacts')
      }
    };
    $scope.newMilestonesPanel = {
      name: 'milestones',
      config: {},
      query: {
        entity: 'Activity',
        action: 'getcontactactivities',
        countAction: 'getcontactactivitiescount',
        params: getQueryParams('milestones')
      },
      custom: {
        itemName: 'milestones',
        involvementFilter: { '@involvingContact': 'myActivities' },
        cardRefresh: activityCardRefreshMilestones
      },
      handlers: {
        range: _.curry(rangeHandler)('activity_date_time')('YYYY-MM-DD HH:mm:ss')(true),
        results: _.curry(resultsHandler)(formatActivity)('case_id.contacts')
      }
    };
    $scope.newCasesPanel = {
      config: {},
      custom: {
        itemName: ts('cases'),
        caseClick: casesCustomClick,
        viewCasesLink: viewCasesLink()
      },
      query: { entity: 'Case', action: 'getcaselist', countAction: 'getdetailscount', params: getQueryParams('cases') },
      handlers: {
        range: _.curry(rangeHandler)('start_date')('YYYY-MM-DD')(false),
        results: _.curry(resultsHandler)(formatCase)('contacts')
      }
    };

    $scope.activityCardRefreshCalendar = activityCardRefreshCalendar;

    (function init () {
      initWatchers();
      setCalendarParams();
    }());

    /**
     * Refresh callback triggered by activity cards in the activities panel
     *
     * @param {Array} apiCalls api calls
     */
    function activityCardRefreshActivities (apiCalls) {
      activityCardRefresh($scope.activitiesPanel.name, apiCalls);
    }

    /**
     * Refresh callback triggered by activity cards in the calendar
     *
     * @param {Array} apiCalls api calls
     */
    function activityCardRefreshCalendar (apiCalls) {
      activityCardRefresh([
        $scope.activitiesPanel.name,
        $scope.newMilestonesPanel.name
      ], apiCalls);
    }

    /**
     * Refresh callback triggered by activity cards in the milestones panel
     *
     * @param {Array} apiCalls api calls
     */
    function activityCardRefreshMilestones (apiCalls) {
      activityCardRefresh($scope.newMilestonesPanel.name, apiCalls);
    }

    /**
     * The common refresh callback logic triggered by the activity cards in the
     * dashboard It reloads of the calendar and the panel(s) with the given
     * name(s)
     *
     * Unfortunately the activity card expects the callback to handle api calls
     * for it, hence the `apiCalls` param and the usage of `crmApi`
     *
     * @see {@link https://github.com/compucorp/uk.co.compucorp.civicase/blob/develop/ang/civicase/ActivityCard.js#L97}
     *
     * @param {Array/string} panelName the name of the panel to refresh
     * @param {Array} apiCalls api calls
     */
    function activityCardRefresh (panelName, apiCalls) {
      if (!_.isArray(apiCalls)) {
        apiCalls = [];
      }

      crmApi(apiCalls).then(function (result) {
        $rootScope.$emit('civicase::ActivitiesCalendar::reload');
        $rootScope.$emit('civicase::PanelQuery::reload', panelName);
      });
    }

    /**
     * Click handler that redirects the browser to the given case's details page
     *
     * @param {object} caseObj case object
     */
    function casesCustomClick (caseObj) {
      $location.path('case/list').search('caseId', caseObj.id);
    }

    /**
     * Get the processed list of query pars of the given collection (cases,
     * milestones, etc)
     *
     * @param {string} collection collection
     * @returns {object} params
     */
    function getQueryParams (collection) {
      var activityFiltersCopy = _.cloneDeep($scope.activityFilters);

      // Cases need the properties of `activityFilters.case_filter` in the
      // object root
      return _.assign({}, defaultsMap[collection], (collection === 'cases'
        ? activityFiltersCopy.case_filter
        : activityFiltersCopy
      ));
    }

    /**
     * Initializes the controller watchers
     */
    function initWatchers () {
      $scope.$on('civicase::dashboard-filters::updated', function () {
        refresh(true);
      });
      $scope.$watchCollection('filters.caseRelationshipType', function (newType, oldType) {
        if (newType === oldType) {
          return;
        }

        refresh();
      });

      // When the involvement filters change, broadcast the event that will be
      // caught by the activity-filters-contact directive which will add the
      // correct query params to match the filter value
      $scope.$watch('newMilestonesPanel.custom.involvementFilter', function (newValue, oldValue) {
        if (newValue === oldValue) {
          return;
        }

        updatePanelQueryActions($scope.newMilestonesPanel);
        $rootScope.$broadcast(
          'civicaseActivityFeed.query', {
            filters: $scope.newMilestonesPanel.custom.involvementFilter,
            apiParams: $scope.newMilestonesPanel.query.params,
            reset: true
          }
        );
      }, true);

      $scope.$watch('activitiesPanel.custom.involvementFilter', function (newValue, oldValue) {
        if (newValue === oldValue) {
          return;
        }

        updatePanelQueryActions($scope.activitiesPanel);
        $rootScope.$broadcast(
          'civicaseActivityFeed.query', {
            filters: $scope.activitiesPanel.custom.involvementFilter,
            apiParams: $scope.activitiesPanel.query.params,
            reset: true
          }
        );
      }, true);
    }

    /**
     * Refresh all panels
     *
     * @param {boolean} forceReload whether to force reload all panels
     */
    function refresh (forceReload) {
      $scope.activitiesPanel.query.params = getQueryParams('activities');
      $scope.newMilestonesPanel.query.params = getQueryParams('milestones');
      $scope.newCasesPanel.query.params = getQueryParams('cases');

      $scope.activitiesPanel.config.forceReload = forceReload;
      $scope.newMilestonesPanel.config.forceReload = forceReload;
      $scope.newCasesPanel.config.forceReload = forceReload;

      $scope.newCasesPanel.custom.viewCasesLink = viewCasesLink();

      setCalendarParams();
    }

    /**
     * It fetches and stores the ids of all the open cases that match the
     * current relationship filter's value.
     *
     * The ids are used for the activities calendar
     */
    function setCalendarParams () {
      $scope.calendarCaseParams = _.assign({
        'status_id.grouping': 'Opened'
      }, $scope.activityFilters.case_filter);
    }

    /**
     * Sets the range of the date of the entity (Case / Activity)
     *
     * @param {string} property the property where the information about the
     *   date is stored
     * @param {string} format the date format
     * @param {boolean} useNowAsStart whether the starting point should be the
     *   current datetime
     * @param {string} selectedRange the currently selected period range
     * @param {object} queryParams params
     */
    function rangeHandler (property, format, useNowAsStart, selectedRange, queryParams) {
      var now = moment();
      var start = (useNowAsStart ? now : now.startOf(selectedRange)).format(format);
      var end = now.endOf(selectedRange).format(format);

      queryParams[property] = { BETWEEN: [start, end] };
    }

    /**
     * Formats each results (whether a case or an entity) returned by the api
     * call, and fetches the data of all the contacts referenced in the list
     *
     * @param {Function} formatFn the function that will do the formatting
     * @param {string} contactsProp the property where the list of contacts is
     *   stored
     * @param {Array} results the list of results
     * @returns {Promise} results
     */
    function resultsHandler (formatFn, contactsProp, results) {
      // Flattened list of all the contact ids of all the contacts of all the cases
      var contactIds = _(results).pluck(contactsProp).flatten().pluck('contact_id').uniq().value();
      var formattedResults = _.map(results, formatFn);
      // The try/catch block is necessary because the service does not
      // return a Promise if it doesn't find any new contacts to fetch
      try {
        return ContactsCache.add(contactIds)
          .then(function () {
            return formattedResults;
          });
      } catch (e) {
        return formattedResults;
      }
    }

    /**
     * Updates the action and count action for the given panel query data
     * depending on the selected filter. When filtering by "My Activities" the
     * action is "getcontactactivities" and "getcontactactivitiescount",
     * otherwise it's "get" and "getcount".
     *
     * @param {object} panelQueryData panel query data
     */
    function updatePanelQueryActions (panelQueryData) {
      var defaultActions = { action: 'get', countAction: 'getcount' };
      var myActivityActions = { action: 'getcontactactivities', countAction: 'getcontactactivitiescount' };
      var isRequestingMyActivities = panelQueryData.custom.involvementFilter['@involvingContact'] === 'myActivities';

      $.extend(panelQueryData.query, isRequestingMyActivities ? myActivityActions : defaultActions);
    }

    /**
     * Returns an object representing the "view cases" link
     *
     * Depending on the value of the relationship type filter, both the label
     * and the url of the link might change
     *
     * This function is being called directly on the view so that the object
     * is updated automatically whenever the relationship type filter value
     * changes
     *
     * @returns {object} cases link object
     */
    function viewCasesLink () {
      var queryParams = viewCasesQueryParams();

      return {
        url: $sce.trustAsResourceUrl('#/case/list?' + $.param(queryParams)),
        label: $scope.filters.caseRelationshipType === 'all'
          ? 'View all ' + ts('cases')
          : 'View all my ' + ts('cases')
      };
    }

    /**
     * Returns the query string params for the "view cases" link
     *
     * The only query string parameter needed by the link is
     *   `cf.case_manager` if the relationship type filter is set on "My Cases"
     *   `cf.contact_id` if the relationship type filter is set on "Cases I'm
     * involved in""
     *
     * If the relationship type filter is set on "All Cases", then
     * no parameter is needed
     *
     * @returns {object} parameters
     */
    function viewCasesQueryParams () {
      var params = {};

      if ($scope.filters.caseRelationshipType !== 'all') {
        params.cf = {};

        // @NOTE: The case list page expects the param's value to be
        // inside an array (`case_filter.contact_id` already is)
        if ($scope.filters.caseRelationshipType === 'is_case_manager') {
          params.cf.case_manager = [$scope.activityFilters.case_filter.case_manager];
        } else {
          params.cf.contact_id = $scope.activityFilters.case_filter.contact_id;
        }

        params.cf = JSON.stringify(params.cf);
      }

      return params;
    }
  }
})(angular, CRM.$, CRM._);
