(function (angular) {
  var module = angular.module('civicase');

  module.service('ResumeDraftActivityAction', ResumeDraftActivityAction);

  /**
   * Resume Draft Action
   *
   * @param {object} $rootScope rootscope object
   * @param {object} viewInPopup common factory to open an activity in a popup
   * @param {object} ActivityType activity type service
   */
  function ResumeDraftActivityAction ($rootScope, viewInPopup, ActivityType) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      var isActivityTabBulkAction = $scope.mode === 'case-activity-bulk-action';
      var isFilesTabBulkAction = $scope.mode === 'case-files-activity-bulk-action';

      if (!isActivityTabBulkAction && !isFilesTabBulkAction) {
        var activityTypeId = $scope.selectedActivities[0].activity_type_id;
        var activityTypeName = ActivityType.findById(activityTypeId).name;

        var isDraftEmailOrPdfTypeActivity =
          (activityTypeName === 'Email' || activityTypeName === 'Print PDF Letter') &&
          $scope.selectedActivities[0].status_name === 'Draft';

        return isDraftEmailOrPdfTypeActivity;
      }
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      viewInPopup(null, $scope.selectedActivities[0])
        .on('crmFormSuccess', function () {
          $rootScope.$broadcast('civicase::activity::updated');
        });
    };
  }
})(angular);
