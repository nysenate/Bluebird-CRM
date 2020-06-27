(function ($, _, angular) {
  var module = angular.module('civicase');

  module.directive('civicaseAddActivityMenu', function () {
    return {
      restrict: 'E',
      scope: {
        case: '=',
        excludeActivitiesBy: '@',
        filterActivitiesBy: '@',
        name: '='
      },
      controller: 'civicaseAddActivityMenuController',
      templateUrl: '~/civicase/activity/directives/add-activity-menu.directive.html'
    };
  });

  module.controller('civicaseAddActivityMenuController', function ($scope, getCaseQueryParams, CaseType, ActivityType,
    ActivityForms) {
    var definition = CaseType.getAll()[$scope.case.case_type_id].definition;

    (function init () {
      if (_.isEmpty($scope.case.activity_count)) {
        $scope.case.activity_count = {};
        $scope.availableActivityTypes = getAvailableActivityTypes(
          $scope.case.activity_count, definition);
      } else {
        initWatchers();
      }
    })();

    /**
     * Returns a list of activity types that can be created for the case. Cases
     * activities can have a maximum count which must be respected.
     *
     * @param {object} activityCount the list of activity types and their count for the given case.
     * @param {object} definition the case type definition for the given case.
     * @returns {Array} activity types
     */
    function getAvailableActivityTypes (activityCount, definition) {
      var ret = [];
      var exclude = ['Change Case Status', 'Change Case Type'];

      _.each(definition.activityTypes, function (actSpec) {
        if (exclude.indexOf(actSpec.name) < 0) {
          var actTypeId = _.findKey(ActivityType.getAll(true), { name: actSpec.name });
          var ifActivityTypeIsActive = ActivityType.findById(actTypeId).is_active === '1';

          if (ifActivityTypeIsActive &&
            (!actSpec.max_instances || !activityCount[actTypeId] || (actSpec.max_instances > parseInt(activityCount[actTypeId])))) {
            ret.push($.extend({ id: actTypeId }, ActivityType.getAll()[actTypeId]));
          }
        }
      });

      if ($scope.excludeActivitiesBy) {
        ret = _.filter(ret, function (activity) {
          return !_.includes($scope.excludeActivitiesBy, activity.grouping);
        });
      }

      if ($scope.filterActivitiesBy) {
        ret = _.filter(ret, function (activity) {
          return _.includes($scope.filterActivitiesBy, activity.grouping);
        });
      }

      return _.sortBy(ret, 'label');
    }

    /**
     * Initialise watchers
     */
    function initWatchers () {
      $scope.$watch('case.definition', function (definition) {
        if (!definition) {
          return;
        }

        $scope.availableActivityTypes = getAvailableActivityTypes(
          $scope.case.activity_count, definition);
      });

      $scope.$watch('case.allActivities', function () {
        $scope.availableActivityTypes = getAvailableActivityTypes(
          $scope.case.activity_count, definition);
      });
    }

    /**
     * Returns the URL with the form necessary to create a particular activity for the case.
     *
     * @param {object} actType activity Type
     * @returns {string} url
     */
    $scope.newActivityUrl = function (actType) {
      var caseType = CaseType.getById($scope.case.case_type_id);
      var caseQueryParams = JSON.stringify(getCaseQueryParams({
        caseId: $scope.case.id,
        caseTypeCategory: caseType.case_type_category
      }));
      var newActivity = {
        activity_type_id: actType.id,
        case_id: $scope.case.id,
        type: actType.name
      };
      var options = {
        action: 'add',
        civicase_reload: caseQueryParams
      };
      var activityForm = ActivityForms.getActivityFormService(newActivity, options);

      if (!activityForm) {
        return;
      }

      return activityForm.getActivityFormUrl(newActivity, options);
    };
  });
})(CRM.$, CRM._, angular);
