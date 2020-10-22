(function (angular, CRM, _) {
  var module = angular.module('civicase');

  module.service('EditActivityAction', EditActivityAction);

  /**
   * Edit Activity Action
   *
   * @param {object} ActivityType service to fetch Activity Types
   */
  function EditActivityAction (ActivityType) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      if ($scope.mode === 'case-summary') {
        var activityTypes = ActivityType.getAll(true);
        var selectedActivityType = activityTypes[$scope.selectedActivities[0].activity_type_id];

        var nonEditableActivityTypes = [
          'Email',
          'Print PDF Letter'
        ];

        return !_.includes(nonEditableActivityTypes, selectedActivityType.name) && !!$scope.getEditActivityUrl;
      }
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     */
    this.doAction = function ($scope) {
      CRM.loadForm($scope.getEditActivityUrl($scope.selectedActivities[0].id));
    };
  }
})(angular, CRM, CRM._);
