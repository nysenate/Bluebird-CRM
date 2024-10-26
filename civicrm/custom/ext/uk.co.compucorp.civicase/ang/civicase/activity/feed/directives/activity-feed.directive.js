(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseActivityFeed', function ($timeout, ActivityFeedMeasurements) {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/activity/feed/directives/activity-feed.directive.html',
      controller: civicaseActivityFeedController,
      link: civicaseActivityFeedLink,
      scope: {
        params: '=civicaseActivityFeed',
        showBulkActions: '=',
        canSelectCaseTypeCategory: '=',
        caseTypeId: '=',
        refreshCase: '=?refreshCallback',
        hideQuickNavWhenDetailsIsVisible: '='
      }
    };

    /**
     * Link function for civicaseActivityFeed
     *
     * @param {object} scope scope object
     * @param {object} element direcitve element
     * @param {object} attrs attributes
     */
    function civicaseActivityFeedLink (scope, element, attrs) {
      (function init () {
        scope.$watch('isLoading', checkIfLoadingCompleted);
      }());

      /**
       * Check if loading is complete
       */
      function checkIfLoadingCompleted () {
        if (!scope.isLoading) {
          $timeout(setListHeight);
        }
      }

      /**
       * Set height for activity list
       */
      function setListHeight () {
        var $feedList = $('.civicase__activity-feed__body__list');

        ActivityFeedMeasurements.setScrollHeightOf($feedList);
      }
    }
  });

  module.controller('civicaseActivityFeedController', civicaseActivityFeedController);

  /**
   *
   * @param {object} $scope scope object
   * @param {object} $q $q service
   * @param {object} BulkActions bulk actions service
   * @param {object} civicaseCrmApi crm api service
   * @param {object} crmUiHelp crm ui help service
   * @param {object} crmThrottle crm throttle service
   * @param {object} formatActivity format activity service
   * @param {object} $rootScope root scope object
   * @param {object} dialogService dialog service
   * @param {object} ContactsCache contacts cache service
   * @param {object} ActivityStatus activity status service
   * @param {object} ActivityType activity type service
   * @param {object} CaseType case type service
   * @param {boolean} showFullContactNameOnActivityFeed Show Full Contact Name On Activity Feed setting
   */
  function civicaseActivityFeedController ($scope, $q, BulkActions, civicaseCrmApi,
    crmUiHelp, crmThrottle, formatActivity, $rootScope, dialogService,
    ContactsCache, ActivityStatus, ActivityType, CaseType, showFullContactNameOnActivityFeed) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('civicase');
    var ITEMS_PER_PAGE = 25;
    var activityStartingOffset = 0;
    var caseId = $scope.params ? $scope.params.case_id : null;
    var pageNum = { down: 0, up: 0 };
    var activitySets = $scope.caseTypeId &&
      CaseType.getById($scope.caseTypeId).definition.activitySets;

    $scope.filters = {};
    $scope.isMonthNavVisible = true;
    $scope.isLoading = true;
    $scope.isSelectAll = false;
    $scope.activityTypes = ActivityType.getAll(true);
    $scope.activityStatuses = ActivityStatus.getAll();
    $scope.activities = {};
    $scope.activityGroups = [];
    $scope.bulkAllowed = $scope.showBulkActions && BulkActions.isAllowed();
    $scope.selectedActivities = [];
    $scope.viewingActivity = {};
    $scope.refreshAll = refreshAll;
    $scope.showFullContactNameOnActivityFeed = showFullContactNameOnActivityFeed;
    $scope.showSpinner = { up: false, down: false };
    $scope.caseTimelines = $scope.caseTypeId
      ? _.sortBy(activitySets, 'label')
      : [];

    (function init () {
      applyFiltersFromBindings();
      bindRouteParamsToScope();
      initiateWatchersAndEvents();
    }());

    /**
     * Check if more records are vailable in the given direction
     *
     * @param {string} direction direction
     * @returns {boolean} yes/no
     */
    $scope.checkIfRecordsAvailableOnDirection = function (direction) {
      if ($scope.totalCount === 0) {
        return false;
      }

      var lastRecordShownInGivenDirection;

      if (direction === 'down') {
        lastRecordShownInGivenDirection = (activityStartingOffset + (ITEMS_PER_PAGE * (pageNum.down + 1)));

        return lastRecordShownInGivenDirection < $scope.totalCount;
      } else if (direction === 'up') {
        lastRecordShownInGivenDirection = (activityStartingOffset - (ITEMS_PER_PAGE * pageNum.up));

        return lastRecordShownInGivenDirection > 0;
      }
    };

    /**
     * Find activity by given ID
     *
     * @param {Array} searchIn - array of activities to search into
     * @param {number/string} activityID activityID
     * @returns {object} activity
     */
    $scope.findActivityById = function (searchIn, activityID) {
      return _.find(searchIn, { id: activityID });
    };

    /**
     * Toggle Bulk Actions checkbox of the given activity
     *
     * @param {object} activity activity
     */
    $scope.toggleSelected = function (activity) {
      if ($scope.isSelectAll) {
        deselectAllActivities();
      }

      if (!$scope.findActivityById($scope.selectedActivities, activity.id)) {
        $scope.selectedActivities.push($scope.findActivityById($scope.activities, activity.id));
      } else {
        _.remove($scope.selectedActivities, { id: activity.id });
      }
    };

    /**
     * Load next set of activities
     */
    $scope.nextPage = function () {
      ++pageNum.down;
      getActivities({ direction: 'down', nextPage: true });
    };

    /**
     * Load previous set of activities
     */
    $scope.previousPage = function () {
      ++pageNum.up;
      getActivities({ direction: 'up' });
    };

    /**
     * View an activity in details view
     *
     * @param {number} id id
     * @param {object} e event
     */
    $scope.viewActivity = function (id, e) {
      if (e && $(e.target).closest('a, button').length) {
        return;
      }
      var act = _.find($scope.activities, { id: id });
      // If the same activity is selected twice, it's a deselection. If the activity doesn't exist, abort.
      if (($scope.viewingActivity && $scope.viewingActivity.id === id) || !act) {
        $scope.viewingActivity = {};
        $scope.aid = 0;
        $rootScope.$broadcast('civicase::activity-feed::hide-activity-panel');
      } else {
        // Mark email read
        if (act.status === 'Unread') {
          var statusId = _.findKey(ActivityStatus.getAll(), { name: 'Completed' });
          civicaseCrmApi(
            'Activity',
            'setvalue',
            { id: act.id, field: 'status_id', value: statusId }
          ).then(function () {
            act.status_id = statusId;
            formatActivity(act);
          });
        }
        $scope.viewingActivity = _.cloneDeep(act);
        $rootScope.$broadcast('civicase::activity-feed::show-activity-panel', $scope.viewingActivity);
        $scope.aid = act.id;
      }
    };

    /**
     * Accepts filters coming from the scope bindings and will add them
     * to the local filters object.
     */
    function applyFiltersFromBindings () {
      if ($scope.params && $scope.params.filters) {
        angular.extend($scope.filters, $scope.params.filters);
      }
    }

    /**
     * Binds all route parameters to scope
     */
    function bindRouteParamsToScope () {
      $scope.$bindToRoute({ param: 'aid', expr: 'aid', format: 'raw', default: 0 });
      $scope.$bindToRoute({ expr: 'filters', param: 'af', default: $scope.filters });
      $scope.$bindToRoute({
        expr: 'displayOptions',
        param: 'ado',
        default: angular.extend({}, {
          followup_nested: true,
          overdue_first: true,
          include_case: true
        }, $scope.params.displayOptions || {})
      });
    }

    /**
     * Bulk Selection Event Listener
     *
     * @param {object} event event
     * @param {string} condition condition
     */
    function bulkSelectionsListener (event, condition) {
      if (condition === 'none') {
        deselectAllActivities();
      } else if (condition === 'visible') {
        selectDisplayedActivities();
      } else if (condition === 'all') {
        selectEveryActivity();
      }
    }

    /**
     * Sets the activities array based on direction
     *
     * @param {string} mode mode
     * @param {Array} newActivities new activities
     */
    function buildActivitiesArray (mode, newActivities) {
      if (mode.direction === 'down') {
        if (pageNum.down) {
          $scope.activities = $scope.activities.concat(newActivities);
        } else {
          $scope.activities = newActivities;
        }
      } else if (mode.direction === 'up') {
        $scope.activities = newActivities.concat($scope.activities);
      }
    }

    /**
     * Deselection of all activities
     */
    function deselectAllActivities () {
      $scope.isSelectAll = false;
      $scope.selectedActivities = [];
    }

    /**
     * Select all Activity
     */
    function selectEveryActivity () {
      deselectAllActivities();
      $scope.isSelectAll = true;
    }

    /**
     * Select All visible data.
     */
    function selectDisplayedActivities () {
      $scope.isSelectAll = false;
      var isCurrentActivityInSelectedCases;

      _.each($scope.activities, function (activity) {
        isCurrentActivityInSelectedCases = $scope.findActivityById($scope.selectedActivities, activity.id);

        if (!isCurrentActivityInSelectedCases) {
          $scope.selectedActivities.push($scope.findActivityById($scope.activities, activity.id));
        }
      });
    }

    /**
     * Groups the activities into Overdue/Future/Past
     *
     * @param {Array} activities activities
     * @returns {Array} grouped activities
     */
    function groupActivities (activities) {
      var group, overdue, upcoming, past;
      var groups = [];
      var date = new Date();
      var now = CRM.utils.formatDate(date, 'yy-mm-dd') + ' ' + date.toTimeString().slice(0, 8);

      if ($scope.displayOptions.overdue_first) {
        groups.push(overdue = { key: 'overdue', title: ts('Overdue Activities'), activities: [] });
      }

      groups.push(upcoming = { key: 'upcoming', title: ts('Future Activities'), activities: [] });
      groups.push(past = { key: 'past', title: ts('Past Activities - Prior to Today'), activities: [] });
      _.each(activities, function (act, index) {
        if (act.activity_date_time > now) {
          group = upcoming;
        } else if (overdue && act.is_overdue) {
          group = overdue;
        } else {
          group = past;
        }
        // identification of the card, used in scroll into view for quick nav
        act.cardId = (activityStartingOffset - (pageNum.up * ITEMS_PER_PAGE)) + index;

        group.activities.push(act);
      });

      return groups;
    }

    /**
     * Fetch additional information about the contacts
     *
     * @param {Array} activities activities
     * @returns {Promise} Promise
     */
    function fetchContactsData (activities) {
      var contacts = [];

      _.each(activities, function (activity) {
        contacts = contacts.concat(activity.assignee_contact_id);
        contacts = contacts.concat(activity.target_contact_id);

        if (activity['case_id.contacts']) {
          contacts = contacts.concat(activity['case_id.contacts'].map(function (contact) {
            return contact.contact_id;
          }));
        }

        contacts.push(activity.source_contact_id);
      });

      return ContactsCache.add(contacts);
    }

    /**
     * Get all activities
     *
     * @param {object} mode mode
     * @returns {Promise} Promise
     */
    function getActivities (mode) {
      showSpinner(mode);

      if (mode.nextPage !== true && mode.direction !== 'up' && !mode.monthNavClicked) {
        deselectAllActivities();
        pageNum.down = 0;
      }

      return crmThrottle(function () {
        return loadActivities(mode);
      }).then(function (result) {
        var newActivities = _.each(result[0].acts.values, formatActivity);

        buildActivitiesArray(mode, newActivities);

        $scope.activityGroups = groupActivities($scope.activities);
        $scope.totalCount = result[0].all;
        // reset viewingActivity to get latest data
        $scope.viewingActivity = {};
        $scope.viewActivity($scope.aid);
        $scope.isLoading = false;
        $scope.showSpinner.up = false;
        $scope.showSpinner.down = false;
      });
    }

    /**
     * Load activities.
     *
     * Note: When the filter is set to "My Activities" the action
     * used is `getcontactactivities` instead of `get` since this action
     * properly returns activities that belong to the contact, but have not
     * been delegated to someone else. This query can't be replicated using
     * api params hence the need for a specialized action.
     *
     * @param {string} mode mode
     * @returns {Promise} Promise
     */
    function loadActivities (mode) {
      var options = setActivityAPIOptions(mode);
      var isMyActivitiesFilter = $scope.filters['@involvingContact'] === 'myActivities';
      var apiAction = isMyActivitiesFilter ? 'getcontactactivities' : 'getAll';
      var apiActionAll = isMyActivitiesFilter ? 'getcontactactivitiescount' : 'getcount';
      var returnParams = {
        sequential: 1,
        return: [
          'subject', 'details', 'activity_type_id', 'status_id',
          'source_contact_name', 'target_contact_name', 'assignee_contact_name',
          'activity_date_time', 'is_star', 'original_id', 'tag_id.name', 'tag_id.description',
          'tag_id.color', 'file_id', 'is_overdue', 'case_id', 'priority_id', 'case_id.contacts'
        ],
        options: options
      };
      var params = {
        is_current_revision: 1,
        is_deleted: 0,
        is_test: 0,
        options: {}
      };

      if (caseId) {
        params.case_id = caseId;
      } else if (!$scope.displayOptions.include_case) {
        params.case_id = { 'IS NULL': 1 };
      } else {
        returnParams.return = returnParams.return.concat(['case_id.case_type_id', 'case_id.status_id']);
      }

      _.each($scope.filters, function (val, key) {
        if (key[0] === '@') return; // Virtual params.
        // keys which starts with `$`, are applied directly after removing `$`
        if (key[0] === '$') {
          var paramKey = key.substr(1, key.length);
          params[paramKey] = val;

          return;
        }
        if (key === 'activity_type_id' || key === 'activitySet') {
          setActivityTypeIDsFilter(params);
        } else if (key === 'case_type_category') {
          if (val.length > 0 && $scope.displayOptions.include_case) {
            params.case_filter = params.case_filter || {};
            params.case_filter['case_type_id.case_type_category'] = val;
          }
        } else if (val) {
          if (key === 'text') {
            params.subject = { LIKE: '%' + val + '%' };
            params.details = { LIKE: '%' + val + '%' };
            params.options.or = [['subject', 'details']];
          } else if (_.isString(val)) {
            params[key] = { LIKE: '%' + val + '%' };
          } else if (_.isArray(val) && val.length) {
            params[key] = val.length === 1 ? val[0] : { IN: val };
          } else if (!_.isArray(val)) {
            params[key] = val;
          }
        }
      });

      $rootScope.$broadcast(
        'civicaseActivityFeed.query', {
          filters: $scope.filters,
          apiParams: params,
          reset: false,
          overdueFirst: $scope.displayOptions.overdue_first,
          isMyActivitiesFilter: isMyActivitiesFilter
        }
      );

      return civicaseCrmApi({
        acts: ['Activity', apiAction, prepareActivityParams(returnParams, params)],
        all: ['Activity', apiActionAll, params]
      }).then(function (result) {
        return $q.all([
          result,
          fetchContactsData(result.acts.values)
        ]);
      });
    }

    /**
     * Initiate watchers and event handlers
     */
    function initiateWatchersAndEvents () {
      $scope.$watchCollection('filters', resetPages);
      $scope.$watchCollection('displayOptions', resetPages);
      $scope.$on('updateCaseData', resetPages);
      $scope.$on('civicase::activity::updated', refreshAll);
      $scope.$on('civicase::month-nav::set-starting-offset', monthNavSetStartingOffsetListener);
      $scope.$on('civicase::bulk-actions::bulk-selections', bulkSelectionsListener);
      $scope.$on('civicaseAcitivityClicked', function (event, $event, activity) {
        $scope.viewActivity(activity.id, $event);
      });
      $scope.$on('civicase::activity-feed::show-activity-panel', function () {
        toggleMonthNavVisibility(false);
      });
      $scope.$on('civicase::activity-feed::hide-activity-panel', function () {
        toggleMonthNavVisibility(true);
      });
      $scope.$watch('params.filters', function () {
        applyFiltersFromBindings();
        resetPages();
      }, true);
    }

    /**
     * Listener for 'civicase::month-nav::set-starting-offset' event
     *
     * @param {object} event event
     * @param {object} param param
     */
    function monthNavSetStartingOffsetListener (event, param) {
      pageNum.up = 0;
      pageNum.down = 0;
      activityStartingOffset = param.startingOffset;

      getActivities({
        direction: 'down',
        monthNavClicked: true
      });
    }

    /**
     * Prepares Activity Params
     * Adds Action Links chained Api call
     *
     * @param {object} returnParams returnParams
     * @param {object} params params
     *
     * @returns {Array} params
     */
    function prepareActivityParams (returnParams, params) {
      var actionLinksParams = {
        'api.Activity.getactionlinks': {
          activity_id: '$value.id',
          activity_type_id: '$value.activity_type_id',
          source_record_id: '$value.source_record_id',
          case_id: '$value.case_id'
        }
      };

      return $.extend(true, actionLinksParams, returnParams, params);
    }

    /**
     * Resets the pages and calls 'getActivities'
     */
    function resetPages () {
      activityStartingOffset = 0;
      pageNum.up = 0;

      getActivities({ direction: 'down' });
    }

    /**
     * Refresh Activities
     * If: refreshCase callback is passed to the directive, calls the same
     * Else: Calls civicaseCrmApi directly
     *
     * @param {Array} apiCalls apiCalls
     */
    function refreshAll (apiCalls) {
      if (_.isFunction($scope.refreshCase)) {
        $scope.refreshCase(apiCalls);
      } else {
        if (!_.isArray(apiCalls)) {
          apiCalls = [];
        }

        civicaseCrmApi(apiCalls, true).then(function (result) {
          getActivities({ direction: 'down' });
        });
      }
    }

    /**
     * Sets the Activity API option parameters
     *
     * @param {object} mode mode
     * @returns {object} options
     */
    function setActivityAPIOptions (mode) {
      var options = {
        sort: ($scope.displayOptions.overdue_first ? 'is_overdue DESC, ' : '') + 'activity_date_time DESC',
        limit: ITEMS_PER_PAGE,
        offset: null
      };

      if (mode.direction === 'down') {
        options.offset = activityStartingOffset + (ITEMS_PER_PAGE * pageNum.down);
      } else if (mode.direction === 'up') {
        options.offset = activityStartingOffset - (ITEMS_PER_PAGE * pageNum.up);

        if (options.offset < 0) {
          options.limit = ITEMS_PER_PAGE + options.offset;
          options.offset = 0;
        }
      }

      return options;
    }

    /**
     * When timeline/activity-set filter is applied,
     * gets the activity type ids from the selected timeline.
     * Also adds those activity types ids with the "Activity Type" filter
     *
     * @param {object} params params
     */
    function setActivityTypeIDsFilter (params) {
      var activityTypeIDs = [];

      if ($scope.filters.activitySet) {
        var activitySet = _.find($scope.caseTimelines, function (activitySet) {
          return activitySet.name === $scope.filters.activitySet;
        });

        if (activitySet) {
          _.each(activitySet.activityTypes, function (activityTypeFromSet) {
            activityTypeIDs.push(_.findKey(ActivityType.getAll(true), function (activitySet) {
              return activitySet.name === activityTypeFromSet.name;
            }));
          });
        }
      }

      // add activity types ids from the "Activity Type" filter
      if ($scope.filters.activity_type_id) {
        activityTypeIDs = activityTypeIDs.concat($scope.filters.activity_type_id);
      }

      if (activityTypeIDs.length) {
        params.activity_type_id = { IN: activityTypeIDs };
      }
    }

    /**
     * Shows the spinner before loading new data
     *
     * @param {object} mode mode
     */
    function showSpinner (mode) {
      if (mode.direction === 'up' || mode.monthNavClicked === true) {
        $scope.showSpinner.up = true;
      } else if (mode.direction === 'down' && mode.nextPage === true) {
        $scope.showSpinner.down = true;
      }
    }

    /**
     * Toggles the visiblity of month nav,
     * when hideQuickNavWhenDetailsIsVisible is true
     *
     * @param {boolean} isMonthNavVisible is month navigation visible
     */
    function toggleMonthNavVisibility (isMonthNavVisible) {
      if ($scope.hideQuickNavWhenDetailsIsVisible) {
        $scope.isMonthNavVisible = isMonthNavVisible;
      }
    }
  }
})(angular, CRM.$, CRM._);
