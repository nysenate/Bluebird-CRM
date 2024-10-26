(function (angular, CRM, _) {
  var module = angular.module('civicase');

  module.service('PrintReportActivityAction', PrintReportActivityAction);

  /**
   * Print Report Activity Action
   *
   * @param {object} $window window object
   */
  function PrintReportActivityAction ($window) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return $scope.mode === 'case-activity-bulk-action' && $scope.isCaseSummaryPage;
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      var url = $scope.getPrintActivityUrl($scope.selectedActivities);

      $window.open(url, '_blank').focus();
    };
  }
})(angular, CRM, CRM._);
