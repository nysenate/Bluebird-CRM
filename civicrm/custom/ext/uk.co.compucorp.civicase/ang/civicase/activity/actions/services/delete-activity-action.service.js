(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('DeleteActivityAction', DeleteActivityAction);

  /**
   * Tags Activity Action Service
   *
   * @param {object} $injector angulars injector service
   * @param {object} $rootScope rootscope object
   * @param {object} civicaseCrmApi service to use civicrm api
   * @param {object} pascalCase pascal case service
   * @param {Function} ts Translation Service
   */
  function DeleteActivityAction ($injector, $rootScope, civicaseCrmApi, pascalCase,
    ts) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return _.every($scope.selectedActivities, function (activity) {
        var activityStatusService = getActivityStatusService(activity.type);

        if (activityStatusService && activityStatusService.isDeleteVisible) {
          return activityStatusService.isDeleteVisible(activity);
        }

        return true;
      });
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      deleteActivity(
        $scope.selectedActivities,
        $scope.isSelectAll,
        $scope.params,
        $scope.totalCount
      );
    };

    /**
     * Delete activities
     *
     * @param {Array} activities list of activities
     * @param {boolean} isSelectAll if select all checkbox is true
     * @param {object} params search parameters for activities to be deleted
     * @param {number} totalCount total number of activities, used when isSelectAll is true
     */
    function deleteActivity (activities, isSelectAll, params, totalCount) {
      var activityLength = isSelectAll ? totalCount : activities.length;

      CRM.confirm({
        title: ts('Delete Activity'),
        message: ts('Permanently delete %1 activit%2?', { 1: activityLength, 2: activityLength > 1 ? 'ies' : 'y' })
      }).on('crmConfirm:yes', function () {
        var apiCalls = prepareApiCalls(activities, isSelectAll, params);

        civicaseCrmApi(apiCalls)
          .then(function () {
            $rootScope.$broadcast('civicase::activity::updated');
          });
      });
    }

    /**
     * Prepare the API calls for the delete operation
     *
     * @param {Array} activities list of activities
     * @param {boolean} isSelectAll if select all checkbox is true
     * @param {object} params search parameters for activities to be deleted
     * @returns {Array} configuration for the api call
     */
    function prepareApiCalls (activities, isSelectAll, params) {
      if (isSelectAll) {
        return [['Activity', 'deletebyquery', { params: params }]];
      } else {
        return [['Activity', 'deletebyquery', {
          id: activities.map(function (activity) {
            return activity.id;
          })
        }]];
      }
    }

    /**
     * Get Activity Status Action Service
     *
     * @param {string} statusName name of the status
     * @returns {object/null} action service
     */
    function getActivityStatusService (statusName) {
      try {
        return $injector.get(pascalCase(statusName) + 'ActivityStatus');
      } catch (e) {
        return null;
      }
    }
  }
})(angular, CRM.$, CRM._);
