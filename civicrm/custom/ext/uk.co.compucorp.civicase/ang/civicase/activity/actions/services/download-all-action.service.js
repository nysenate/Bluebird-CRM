(function (angular, CRM, _) {
  var module = angular.module('civicase');

  module.service('DownloadAllActivityAction', DownloadAllActivityAction);

  /**
   * Edit Activity Action
   *
   * @param {object} $window window object
   */
  function DownloadAllActivityAction ($window) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return $scope.mode === 'case-activity-feed' &&
        $scope.selectedActivities[0].type === 'File Upload';
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      $window.open(CRM.url('civicrm/case/activity/download-all-files', {
        activity_id: $scope.selectedActivities[0].id
      }), '_blank');
    };
  }
})(angular, CRM, CRM._);
