(function (angular) {
  var module = angular.module('civicase');

  module.service('ViewInFeedActivityAction', ViewInFeedActivityAction);

  /**
   * View in Feed Activity Action
   *
   * @param {object} $window window object
   * @param {object} getActivityFeedUrl service to get activity feed url
   */
  function ViewInFeedActivityAction ($window, getActivityFeedUrl) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return $scope.mode === 'case-summary';
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      $window.location.href = getActivityFeedUrl({
        caseId: $scope.selectedActivities[0].case_id,
        activityId: $scope.selectedActivities[0].id
      });
    };
  }
})(angular);
