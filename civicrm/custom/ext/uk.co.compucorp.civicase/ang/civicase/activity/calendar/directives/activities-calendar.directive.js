(function ($, _, angular) {
  var module = angular.module('civicase');

  module.directive('civicaseActivitiesCalendar', function ($timeout, $uibPosition) {
    return {
      scope: {
        caseId: '=',
        caseParams: '=',
        refresh: '=refreshCallback'
      },
      controller: 'civicaseActivitiesCalendarController',
      templateUrl: '~/civicase/activity/calendar/directives/activities-calendar.directive.html',
      restrict: 'E',
      link: civicaseActivitiesCalendarLink
    };

    /**
     * AngularJS's link function for the civicase activity calendar directive.
     *
     * @param {object} $scope scope
     * @param {object} element element
     */
    function civicaseActivitiesCalendarLink ($scope, element) {
      var datepickerScope;
      var bootstrapThemeContainer = $('#bootstrap-theme');
      var popover = element.find('.activities-calendar-popover');
      var popoverArrow = popover.find('.arrow');

      (function init () {
        $scope.$on('civicase::ActivitiesCalendar::openActivitiesPopover', openActivitiesPopover);
        $scope.$on('civicase::ActivitiesCalendar::refreshDatepicker', function () {
          datepickerScope = datepickerScope || element.find('[uib-datepicker]').isolateScope();

          datepickerScope.datepicker.refreshView();
        });
      })();

      /**
       * Adjusts the position of the popover element if hidden by the window's limits.
       * For example, if the popover is hidden by the right window limit, it will position
       * the popover relative to the bottom left of the element.
       *
       * @param {object} element a jQuery reference to the element to position the popover against.
       */
      function adjustPopoverIfHiddenByWindowsLimits (element) {
        var popoverArrowWidth = 22; // needs to be harcoded because how it is defined in Bootstrap
        var isHidden = {
          right: popover.position().left + popover.width() > $(window).width(),
          left: popover.position().left - popover.width() < 0
        };

        if (isHidden.right) {
          adjustPopoverToElement({
            element: element,
            direction: 'bottom-right',
            arrowPosition: 'calc(100% - ' + popoverArrowWidth + 'px)',
            arrowAdjustment: (popoverArrowWidth / 2)
          });
        } else if (isHidden.left) {
          adjustPopoverToElement({
            element: element,
            direction: 'bottom-left',
            arrowPosition: popoverArrowWidth + 'px',
            arrowAdjustment: -(popoverArrowWidth / 2)
          });
        }
      }

      /**
       * Adjusts the popover's position against the provided element and in the desired position direction.
       *
       * @param {object} adjustments adjustments object
       * @param {object} adjustments.element the jQuery reference to the element to position the popover against.
       * @param {string} adjustments.direction the direction to position the popover against. Can be one of top, left, bottom, right,
       *   or combinations such as bottom-right, etc.
       * @param {string} adjustments.arrowPosition the popover's arrow position
       * @param {number} adjustments.arrowAdjustment this value can be used to make small adjustments to the popover
       *   based on the position of the arrow so they can be aligned properly.
       */
      function adjustPopoverToElement (adjustments) {
        var bodyOffset = $uibPosition.positionElements(adjustments.element, popover, adjustments.direction, true);

        popoverArrow.css('left', adjustments.arrowPosition);
        popover.css({
          left: bodyOffset.left - bootstrapThemeContainer.offset().left + adjustments.arrowAdjustment
        });
      }

      /**
       * Closes the activities dropdown but only when clicking outside the popover
       * container. Also unbinds the mouseup event in order to reduce the amount
       * of active DOM event listeners.
       *
       * @param {document#event:mouseup} event DOM event triggered by the user mouse up action.
       */
      function closeActivitiesDropdown (event) {
        // Note: it breaks when checking `popover.is(event.target)`.
        var isNotPopover = !$(event.target).is('.activities-calendar-popover');
        var notInsidePopover = popover.has(event.target).length === 0;

        if (isNotPopover && notInsidePopover) {
          popover.hide();
          $(document).unbind('mouseup', closeActivitiesDropdown);
        }
      }

      /**
       * Displays the popover on top of the calendar's current active day.
       */
      function displayPopoverOnTopOfActiveDay () {
        // the current active day can only be determined in the next cicle:
        $timeout(function () {
          var activeDay = element.find('.uib-day .active');

          popover.show();
          popover.appendTo(bootstrapThemeContainer);

          positionPopoverOnTopOfElement(activeDay);

          // reset popover arrow's alignment:
          popoverArrow.css('left', '50%');

          adjustPopoverIfHiddenByWindowsLimits(activeDay);
        });
      }

      /**
       * Opens up the activities popover and binds the mouseup event in order
       * to close the popover.
       */
      function openActivitiesPopover () {
        displayPopoverOnTopOfActiveDay();
        $(document).bind('mouseup', closeActivitiesDropdown);
      }

      /**
       * Positions the popover on top of the given element
       *
       * @param {object} element a jQuery reference to the element to position the popover against.
       */
      function positionPopoverOnTopOfElement (element) {
        var bodyOffset = $uibPosition.positionElements(element, popover, 'bottom', true);

        popover.css({
          top: bodyOffset.top - bootstrapThemeContainer.offset().top,
          left: bodyOffset.left - bootstrapThemeContainer.offset().left
        });
      }
    }
  });

  module.controller('civicaseActivitiesCalendarController', civicaseActivitiesCalendarController);

  /**
   * Activities Calendar Controller
   *
   * @param {object} $q $q service
   * @param {object} $rootScope root scope object
   * @param {object} $route route service
   * @param {object} $sce sce service
   * @param {object} $scope $scope scope object
   * @param {object} ContactsCache contacts cache service
   * @param {Function} civicaseCrmApi crm api service
   * @param {Function} formatActivity format activity service
   * @param {Function} getActivityFeedUrl get activity feed url service
   * @param {object} ActivityStatusType activity status type service
   */
  function civicaseActivitiesCalendarController ($q, $rootScope, $route, $sce,
    $scope, ContactsCache, civicaseCrmApi, formatActivity, getActivityFeedUrl, ActivityStatusType) {
    var ACTIVITIES_DISPLAY_LIMIT = 25;
    var DEBOUNCE_WAIT = 300;

    var debouncedLoad;
    var daysWithActivities = {};
    var selectedDate = null;
    var incompleteActivityStatusTypes = ActivityStatusType.getAll().incomplete;
    var completeActivityStatusTypes = ActivityStatusType.getAll().completed;

    $scope.activitiesDisplayLimit = ACTIVITIES_DISPLAY_LIMIT;
    $scope.loadingDays = false;
    $scope.loadingActivities = false;
    $scope.selectedActivites = [];
    $scope.selectedDate = null;
    $scope.calendarOptions = {
      customClass: getDayCustomClass,
      formatDay: 'd',
      showWeeks: false,
      startingDay: 1
    };

    $scope.onDateSelected = onDateSelected;
    $scope.seeAllLinkUrl = seeAllLinkUrl;

    (function init () {
      createDebouncedLoad();
      initListeners();
      initWatchers();
    }());

    /**
     * Adds the given days to the internal list of days with activities, grouped
     * by year+month, assigning the given status to each of them.
     *
     * A 'completed' day won't be added to the list, if a day marked with
     * 'incomplete' already exists. The only exception is if the 'completed' day
     * is marked to be flushed.
     *
     * @param {object} days api response
     * @param {string} status status
     * @param {string} yearMonth YYYY-MM format
     */
    function addDays (days, status, yearMonth) {
      daysWithActivities[yearMonth] = daysWithActivities[yearMonth] || {};

      days.reduce(function (acc, date) {
        var keepDay = acc[date] && acc[date].status === 'incomplete' && !acc[date].toFlush;

        acc[date] = keepDay ? acc[date] : {
          status: status,
          activitiesCache: []
        };
        // If this function is ran during a refresh, this ensures that the day
        // won't be removed by the "flush" phase, given that it still has activities
        acc[date].toFlush = false;

        return acc;
      }, daysWithActivities[yearMonth]);
    }

    /**
     * Creates a debounced version of the `load` function
     */
    function createDebouncedLoad () {
      debouncedLoad = _.debounce(function () {
        var args = arguments;

        // Execute the function as part of the digest cycle
        $scope.$apply(function () {
          load.apply(null, args);
        });
      }, DEBOUNCE_WAIT);
    }

    /**
     * Deletes the days with the given status that have not been updated
     * in the last refresh (ie they had activites initially, but now they haven't anymore)
     *
     * @param {string} status status
     * @param {string} yearMonth yearMonth
     */
    function flushDays (status, yearMonth) {
      _.forEach(daysWithActivities[yearMonth], function (day, date) {
        day.status === status && day.toFlush &&
        (delete daysWithActivities[yearMonth][date]);
      });
    }

    /**
     * Formats the given activity to be displayed on an activity card
     *
     * If the calendar is set to display the activities of only one case, then
     * the `case` property is removed from each activity object, so that the footer
     * with the case info won't be displayed on the card
     *
     * @param {object} activity activity
     * @returns {object} formatted activity
     */
    function formatActivityCardData (activity) {
      activity = formatActivity(activity);

      if (!$scope.caseParams) {
        delete activity.case;
      }

      return activity;
    }

    /**
     * Returns the class that the given date should have depending on the status
     * of all the activities for the date.
     *
     * @param {object} params parameters
     * @param {Date}   params.date the given date that requires the class
     * @param {string} params.mode the current viewing mode of the calendar.
     *   can be "day", "month", or "year".
     * @returns {string} class
     */
    function getDayCustomClass (params) {
      var classSuffix, day;
      var isInCurrentMonth = this.datepicker.activeDate.getMonth() === params.date.getMonth();

      if (!isInCurrentMonth && params.mode === 'day') {
        return 'invisible';
      }

      day = getDayWithActivities(params.date);

      if (!day || params.mode !== 'day') {
        return;
      }

      if (day.status === 'completed') {
        classSuffix = 'completed';
      } else if (moment(params.date).isSameOrAfter(moment.now())) {
        classSuffix = 'scheduled';
      } else {
        classSuffix = 'overdue';
      }

      return 'civicase__activities-calendar__day-status civicase__activities-calendar__day-status--' + classSuffix;
    }

    /**
     * Gets the internally stored day with activities (if any) of the given date
     *
     * @param {Date} date date
     * @returns {object/null} day
     */
    function getDayWithActivities (date) {
      var day = moment(date).format('YYYY-MM-DD');

      try {
        return daysWithActivities[getYearMonth(date)][day];
      } catch (e) {
        return null;
      }
    }

    /**
     * Returns the activity filters params for the "see more" link, so that the link
     * sends the user to the activity feed already filtered by the given date
     *
     * @param {Date} date date
     * @returns {object} filters
     */
    function getSeeMoreActivityFilters (date) {
      var dateMoment = moment(date);

      return {
        '@moreFilters': true,
        activity_date_time: {
          BETWEEN: [
            dateMoment.startOf('day').format('YYYY-MM-DD HH:mm:ss'),
            dateMoment.endOf('day').format('YYYY-MM-DD HH:mm:ss')
          ]
        }
      };
    }

    /**
     * Utility function that returns the year+month of the given date in
     * the YYYY-MM format
     *
     * @param {Date} date date
     * @returns {string} date
     */
    function getYearMonth (date) {
      return moment(date).format('YYYY-MM');
    }

    /**
     * Initializes the controller's listeners
     */
    function initListeners () {
      $rootScope.$on('civicase::ActivitiesCalendar::reload', reload);
      $rootScope.$on('civicase::uibDaypicker::monthSelected', function (__, selectedDate) {
        setSelectedDateAndLoad(selectedDate, true);
      });
      $rootScope.$on('civicase::uibDaypicker::compiled', function (__, selectedDate) {
        setSelectedDateAndLoad(selectedDate);
      });
    }

    /**
     * Initializes the controller's watchers
     */
    function initWatchers () {
      // Trigger a full reload if the value of the given case id(s) changes
      $scope.$watch('caseId', function (newValue, oldValue) {
        newValue !== oldValue && reload();
      });
      $scope.$watch('caseParams', function (newValue, oldValue) {
        newValue !== oldValue && reload();
      });
    }

    /**
     * Entry point of the load logic
     *
     * @param {boolean} options [useCache=true]
     */
    function load (options) {
      options = _.defaults({}, options, { useCache: true });

      $scope.loadingDays = true;

      // @NOTE The user could be switching to different dates (in particular, months)
      // in between the first and second request (as they are not made in parallel).
      //
      // The IIFE is then used to keep a reference to the value of `selectedDate`
      // at the moment of invocation, to make sure that both api requests are made
      // for the same date
      //
      // This is also the reason why `date` has to be passed all the way down
      // to the `loadDaysWithActivities` function
      (function (date) {
        if (options.useCache && daysWithActivities[getYearMonth(date)]) {
          return $q.resolve();
        }

        return loadDaysWithActivities(date)
          .then(function (daysGroupedByStatusType) {
            updatesDatesByActivityStatusType(date, daysGroupedByStatusType);

            $scope.$emit('civicase::ActivitiesCalendar::refreshDatepicker');
          });
      }(selectedDate))
        .then(function () {
          $scope.loadingDays = false;
        });
    }

    /**
     * Loads via the api all the activities of the current case, filtered by
     * the given query params
     *
     * @param {object} params params
     * @returns {Promise} resolves to {Array}
     */
    function loadActivities (params) {
      params = params || {};

      if ($scope.caseId) {
        params.case_id = $scope.caseId;
      }

      if ($scope.caseParams) {
        params.case_filter = $scope.caseParams;
      }

      return civicaseCrmApi('Activity', 'get', _.assign(params, {
        return: [
          'subject', 'details', 'activity_type_id', 'status_id',
          'source_contact_name', 'target_contact_name', 'assignee_contact_name',
          'activity_date_time', 'is_star', 'original_id', 'tag_id.name', 'tag_id.description',
          'tag_id.color', 'file_id', 'is_overdue', 'case_id', 'priority_id',
          'case_id.case_type_id', 'case_id.status_id', 'case_id.contacts'
        ],
        activity_type_id: { '!=': 'Bulk Email' },
        sequential: 1,
        options: {
          // We try to get one activity more than the display limit, so we can
          // tell if we need to show the "show more" link in the activities list
          // (if the limit is 25 and we fetched 26, we still show 25 + "show more")
          limit: $scope.activitiesDisplayLimit + 1
        }
      }))
        .then(function (result) {
          return result.values;
        });
    }

    /**
     * Loads the activities of the given date. It checks if the activities are
     * already cached before making an API request
     *
     * @param {Date} date date
     * @returns {Promise} resolves to {Array}
     */
    function loadActivitiesOfDate (date) {
      var dateMoment = moment(date);
      var day = getDayWithActivities(date);

      if (day.activitiesCache.length) {
        return $q.resolve(day.activitiesCache);
      }

      return loadActivities({
        activity_date_time: {
          BETWEEN: [
            dateMoment.format('YYYY-MM-DD') + ' 00:00:00',
            dateMoment.format('YYYY-MM-DD') + ' 23:59:59'
          ]
        }
      })
        .then(function (activities) {
          day.activitiesCache = activities.map(formatActivityCardData);

          return day.activitiesCache;
        });
    }

    /**
     * Load the data of all the contacts referenced by the given activities
     *
     * @param {Array} activities activities
     * @returns {Promise} promise
     */
    function loadContactsOfActivities (activities) {
      var contactIds = _(activities).pluck('case_id.contacts').flatten().pluck('contact_id').value();

      return ContactsCache.add(contactIds);
    }

    /**
     * Loads the days within the month of the given date
     * with at least an activity for complete and incomplete statuses
     *
     * The days are returned in an object containing also the year+month they
     * belong to, so that they can be properly grouped in the internal list of days
     *
     * @param {Date} date date
     * @returns {Promise} promise
     */
    function loadDaysWithActivities (date) {
      var params = {};
      var dateMoment = moment(date);

      params.activity_type_id = { '!=': 'Bulk Email' };
      params.is_deleted = '0';
      params.status_id = { IN: _.union(incompleteActivityStatusTypes, completeActivityStatusTypes) };
      params.activity_date_time = {
        BETWEEN: [
          dateMoment.startOf('month').format('YYYY-MM-DD HH:mm:ss'),
          dateMoment.endOf('month').format('YYYY-MM-DD HH:mm:ss')
        ]
      };

      if ($scope.caseId) {
        params.case_id = $scope.caseId;
      }

      if ($scope.caseParams) {
        params.case_filter = $scope.caseParams;

        // modified date is always updated when updating a case
        // so adding this improves the performance of the api call
        // as unnecessary cases are filtered out
        params.case_filter.modified_date = {
          '>=': params.activity_date_time.BETWEEN[0]
        };
      }

      params.options = { group_by_field: 'status_id' };

      return civicaseCrmApi('Activity', 'getdayswithactivities', params)
        .then(function (result) {
          return result.values;
        })
        .catch(function () {
          return [];
        });
    }

    /**
     *
     * @param {Date} date date
     * @param {object} daysGroupedByStatusType data returned from api
     */
    function updatesDatesByActivityStatusType (date, daysGroupedByStatusType) {
      var datesWithIncompleteStatuses = [];
      var datesWithCompleteStatuses = [];

      _.each(daysGroupedByStatusType, function (datesList, statusTypeId) {
        var statusTypeIdInt = parseInt(statusTypeId, 10);

        if (_.includes(incompleteActivityStatusTypes, statusTypeIdInt)) {
          datesWithIncompleteStatuses = datesWithIncompleteStatuses.concat(datesList);
        } else if (_.includes(completeActivityStatusTypes, statusTypeIdInt)) {
          datesWithCompleteStatuses = datesWithCompleteStatuses.concat(datesList);
        }
      });

      updateDaysList(datesWithIncompleteStatuses, 'incomplete', date);
      updateDaysList(datesWithCompleteStatuses, 'completed', date);
    }

    /**
     * Called when the user clicks on a day on the datepicker directive
     *
     * If the day has any activities on it, it loads the activities and display
     * them in a popover
     */
    function onDateSelected () {
      if (!getDayWithActivities($scope.selectedDate)) {
        return;
      }

      $scope.loadingActivities = true;
      $scope.selectedActivites = [];

      $scope.$emit('civicase::ActivitiesCalendar::openActivitiesPopover');

      loadActivitiesOfDate($scope.selectedDate)
        .then(function (activities) {
          loadContactsOfActivities(activities)
            .then(function () {
              $scope.selectedActivites = activities;
              $scope.loadingActivities = false;
            });
        });
    }

    /**
     * Before calling the main load logic and forcing it to not use the cache, it
     * performs two type of data reset
     *
     * hard reset: all months except the one of the currently selected date get
     * deleted directly from the internal cache
     *
     * soft reset: the month of the currently selected date is not deleted, but
     * its days are marked to be flushed (deleted) later, in case they won't get
     * returned anymore by the next API requests
     *
     * The soft reset avoids removing all the dots at once before even making the
     * API requests, making for a smoother UI experience
     */
    function reload () {
      var currYearMonth = getYearMonth(selectedDate);

      _.each(daysWithActivities, function (days, yearMonth) {
        if (yearMonth === currYearMonth) {
          _.each(daysWithActivities[yearMonth], function (day) {
            day.toFlush = true;
          });
        } else {
          delete daysWithActivities[yearMonth];
        }
      });

      load({ useCache: false });
    }

    /**
     * Creates the url for the "see all" link, based on the given date
     * (the link will send the user to the activity feed, filtered by that date)
     *
     * @param {Date} date date
     * @returns {string} url
     */
    function seeAllLinkUrl (date) {
      var activityFilters = getSeeMoreActivityFilters(date);

      return getActivityFeedUrl({ activityFilters: activityFilters });
    }

    /**
     * Stores the date currently selected on the datepicker
     * and triggers the load logic (debounced, if specified)
     *
     * @param {Date} _selectedDate_ selected date
     * @param {boolean} debounce whether the load logic should be debounced to
     *   avoid flooding the API
     */
    function setSelectedDateAndLoad (_selectedDate_, debounce) {
      selectedDate = _selectedDate_;
      debounce === true ? debouncedLoad() : load();
    }

    /**
     * Updates the internal list of days with activities with the specified status
     * (affects only the days belonging to the year+month of the given date)
     *
     * It adds the given days and deletes those that are marked for deletion
     *
     * @param {object} days api response
     * @param {string} status status
     * @param {Date} date date
     */
    function updateDaysList (days, status, date) {
      var yearMonth = getYearMonth(date);

      addDays(days, status, yearMonth);
      flushDays(status, yearMonth);
    }
  }
})(CRM.$, CRM._, angular);
