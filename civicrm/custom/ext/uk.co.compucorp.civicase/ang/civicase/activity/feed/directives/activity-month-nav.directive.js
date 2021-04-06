(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.directive('civicaseActivityMonthNav', function ($timeout, ActivityFeedMeasurements) {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/activity/feed/directives/activity-month-nav.directive.html',
      controller: 'civicaseActivityMonthNavController',
      link: civicaseActivityMonthNavLink,
      scope: {
        isLoading: '='
      }
    };

    /**
     * Link function for civicaseActivityMonthNav
     *
     * @param {object} scope scope object
     * @param {object} $element directives html element
     * @param {object} attr attributes of the directive
     */
    function civicaseActivityMonthNavLink (scope, $element, attr) {
      (function init () {
        scope.$watch('isLoading', checkIfLoadingCompleted);
      }());

      /**
       * Check if loading is complete
       */
      function checkIfLoadingCompleted () {
        if (!scope.isLoading) {
          $timeout(setNavHeight);
        }
      }

      /**
       * Set height for activity month nav
       */
      function setNavHeight () {
        var $monthNav = $('.civicase__activity-feed__body__month-nav');

        ActivityFeedMeasurements.setScrollHeightOf($monthNav);
      }
    }
  });

  module.controller('civicaseActivityMonthNavController', civicaseActivityMonthNavController);

  /**
   *
   * @param {object} $rootScope rootscope object
   * @param {object} $scope scope object of the controller
   * @param {Function} civicaseCrmApi service to access civicrm api
   */
  function civicaseActivityMonthNavController ($rootScope, $scope, civicaseCrmApi) {
    var previousApiCalls = null;
    var currentlyActiveMonth = null;
    $scope.navigateToMonth = navigateToMonth;

    (function init () {
      initWatchers();
    }());

    /**
     * Checks if the first record of the month is already rendered
     *
     * @param {object} monthObj month object
     * @returns {boolean} if the first record of the month is already rendered
     */
    function checkIfMonthIsAlreadyLoaded (monthObj) {
      var selector = '[data-offset-number="' + monthObj.startingOffset + '"]';

      return $(selector).length > 0;
    }

    /**
     * Subscribe listener for civicaseActivityFeed.query
     *
     * @param {object} event event object
     * @param {object} feedQueryParams query parameters for activity feed
     * @returns {Promise} promise
     */
    function feedQueryListener (event, feedQueryParams) {
      if (feedQueryParams.isMyActivitiesFilter) {
        feedQueryParams.apiParams.isMyActivitiesFilter = feedQueryParams.isMyActivitiesFilter;
      }

      var apiCalls = getAPICalls(
        feedQueryParams.overdueFirst,
        feedQueryParams.apiParams
      );

      // do not re fetch the groups if params are same
      if (previousApiCalls && _.isEqual(apiCalls, previousApiCalls)) {
        return;
      }
      previousApiCalls = apiCalls;

      return civicaseCrmApi(apiCalls).then(function (result) {
        initGroups();

        if (feedQueryParams.overdueFirst) {
          groupOverdueByYear(result.months_with_overdue.values);
          groupOthersByYear(result.months_wo_overdue.values);
        } else {
          groupOthersByYear(result.months.values);
        }

        $scope.groups = _.filter($scope.groups, function (group) {
          return group.records.length > 0;
        });

        setStartingOffsetsAndSort();
      });
    }

    /**
     * Get API calls to load the months for the month nav
     *
     * @param {boolean} overdueFirst if overdues should be displayed first
     * @param {object} params parameters of the api call
     * @returns {Array} list of api calls
     */
    function getAPICalls (overdueFirst, params) {
      var apiCalls;

      if (overdueFirst) {
        apiCalls = {
          months_wo_overdue: [
            'Activity', 'getmonthswithactivities',
            $.extend(true, { is_overdue: 0 }, params)
          ],
          months_with_overdue: [
            'Activity', 'getmonthswithactivities',
            $.extend(true, { is_overdue: 1 }, params)
          ]
        };
      } else {
        apiCalls = {
          months: [
            'Activity', 'getmonthswithactivities', params
          ]
        };
      }

      return apiCalls;
    }

    /**
     * Group Dates into year and month for the given category
     *
     * @param {object} category category object
     * @param {object} dateObject date object
     * @param {boolean} isOverDueGroup if overdue group
     */
    function groupByYearFor (category, dateObject, isOverDueGroup) {
      var yearObject = _.find(category.records, function (yearObj) {
        return yearObj.year === dateObject.year;
      });

      var monthObject = {
        count: dateObject.count,
        isOverDueGroup: !!isOverDueGroup,
        month: dateObject.month,
        year: dateObject.year,
        monthName: moment(dateObject.month, 'MM').format('MMMM')
      };

      if (yearObject) {
        yearObject.months.push(monthObject);
      } else {
        category.records.push({
          year: dateObject.year,
          months: [monthObject]
        });
      }
    }

    /**
     * Group Months into year
     *
     * @param {Array} monthsArray list of months
     */
    function groupOthersByYear (monthsArray) {
      var current = {
        month: moment(new Date()).format('MM'),
        year: moment(new Date()).format('Y')
      };

      _.each(monthsArray, function (dateObject) {
        var categoryNameForCurrentDateObject;

        if (dateObject.year === current.year && dateObject.month === current.month) {
          categoryNameForCurrentDateObject = 'now';
        } else if ((dateObject.year < current.year) ||
          (dateObject.year === current.year && dateObject.month < current.month)) {
          categoryNameForCurrentDateObject = 'past';
        } else if ((dateObject.year > current.year) ||
          (dateObject.year === current.year && dateObject.month > current.month)) {
          categoryNameForCurrentDateObject = 'future';
        }

        var categoryForCurrentDateObject = _.find($scope.groups, function (group) {
          return group.groupName === categoryNameForCurrentDateObject;
        });

        groupByYearFor(categoryForCurrentDateObject, dateObject);
      });
    }

    /**
     * Group Overdue Activity Months into year
     *
     * @param {Array} monthsArray list of months
     */
    function groupOverdueByYear (monthsArray) {
      var overdueGroup = _.find($scope.groups, function (group) {
        return group.groupName === 'overdue';
      });

      _.each(monthsArray, function (dateObject) {
        groupByYearFor(overdueGroup, dateObject, true);
      });
    }

    /**
     * Initialise the Group object for the directive
     */
    function initGroups () {
      $scope.groups = [
        { groupName: 'overdue', records: [] },
        { groupName: 'future', records: [] },
        { groupName: 'now', records: [] },
        { groupName: 'past', records: [] }
      ];
    }

    /**
     * Initialise different watchers
     */
    function initWatchers () {
      $scope.$on('civicaseActivityFeed.query', feedQueryListener);
    }

    /**
     * Click event for the months of the month nav
     * If the month is already rendered in the screen,
     *  scrolls to the first record of the month
     * If Not, emits an event with the starting offset for that month
     *
     * @param {object} monthObj month object
     */
    function navigateToMonth (monthObj) {
      if (currentlyActiveMonth) {
        currentlyActiveMonth.active = false;
      }
      currentlyActiveMonth = monthObj;
      monthObj.active = true;

      if (!checkIfMonthIsAlreadyLoaded(monthObj)) {
        $rootScope.$broadcast('civicase::month-nav::set-starting-offset', {
          startingOffset: monthObj.startingOffset
        });

        // scroll to the first activity
        $('[data-offset-number]').eq(0)[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
      } else {
        scrollAndHighlight(monthObj);
      }
    }

    /**
     * Scrolls and Highlights the first records of the month
     *
     * @param {object} monthObj month object
     */
    function scrollAndHighlight (monthObj) {
      var selector = '[data-offset-number=' + monthObj.startingOffset + ']';
      var element = $(selector);

      element[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
      element.effect('highlight', {}, 3000);
    }

    /**
     * Sets the starting offset for each month and sorts by year and month
     */
    function setStartingOffsetsAndSort () {
      var offset = 0;

      _.each($scope.groups, function (group) {
        group.records = _.sortBy(group.records, function (record) {
          return record.year * -1;
        });

        _.each(group.records, function (record) {
          record.months = _.sortBy(record.months, function (monthObj) {
            return monthObj.month * -1;
          });

          _.each(record.months, function (month) {
            month.startingOffset = offset;
            offset += month.count;
          });
        });
      });
    }
  }
})(angular, CRM.$, CRM._, CRM);
