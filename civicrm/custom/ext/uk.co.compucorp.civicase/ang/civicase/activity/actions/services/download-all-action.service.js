(function (angular, CRM, _) {
  var module = angular.module('civicase');

  module.service('DownloadAllActivityAction', DownloadAllActivityAction);

  /**
   * Edit Activity Action
   *
   * @param {object} $window window object
   * @param {object} civicaseCrmUrl civicrm url service
   */
  function DownloadAllActivityAction ($window, civicaseCrmUrl) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      var isCaseActivityFeedMenu = $scope.mode === 'case-activity-feed-menu';
      var isCaseActivityBulkAction = $scope.mode === 'case-activity-bulk-action';
      var isCaseFilesTabBulkAction = $scope.mode === 'case-files-activity-bulk-action';
      var areAllSelectedActivitiesFileUploadType = _.every($scope.selectedActivities, {
        type: 'File Upload'
      });

      var showActionInActivityFeed = (
        (isCaseActivityFeedMenu || isCaseActivityBulkAction) &&
        areAllSelectedActivitiesFileUploadType &&
        !$scope.isSelectAll
      );

      return showActionInActivityFeed || isCaseFilesTabBulkAction;
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      var downloadAllParams = {};

      var isCaseActivityFeedMenu = $scope.mode === 'case-activity-feed-menu';
      var isCaseActivityBulkAction = $scope.mode === 'case-activity-bulk-action';
      var isCaseFilesTabBulkAction = $scope.mode === 'case-files-activity-bulk-action';

      if (isCaseActivityFeedMenu || isCaseActivityBulkAction) {
        downloadAllParams.activity_ids = _.map($scope.selectedActivities, 'id');
      } else if (isCaseFilesTabBulkAction) {
        if ($scope.isSelectAll) {
          downloadAllParams.searchParams = $scope.params;
        } else {
          downloadAllParams.activity_ids = $scope.selectedActivities.map(function (activity) {
            return activity.id;
          });
        }
      }

      $window.open(civicaseCrmUrl('civicrm/case/activity/download-all-files', downloadAllParams), '_blank');
    };
  }
})(angular, CRM, CRM._);
