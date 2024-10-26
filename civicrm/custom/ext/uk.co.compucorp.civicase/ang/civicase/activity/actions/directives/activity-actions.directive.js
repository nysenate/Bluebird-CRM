(function ($, _, angular) {
  var module = angular.module('civicase');

  module.directive('civicaseActivityActions', function () {
    return {
      scope: {
        mode: '@',
        selectedActivities: '=',
        isSelectAll: '=',
        isReadOnly: '<',
        totalCount: '=',
        params: '='
      },
      require: '?^civicaseCaseDetails',
      controller: civicaseActivityActionsController,
      templateUrl: '~/civicase/activity/actions/directives/activity-actions.directive.html',
      restrict: 'A',
      link: civicaseActivityActionsLink
    };

    /**
     * Angular JS's link function for the directive civicaseActivityActions
     *
     * @param {object} $scope angular scope
     * @param {object} attrs attributes
     * @param {object} element element
     * @param {object} caseDetails case details service
     */
    function civicaseActivityActionsLink ($scope, attrs, element, caseDetails) {
      if (caseDetails) {
        // TODO - Unit test pending
        $scope.isCaseSummaryPage = true;
        $scope.getEditActivityUrl = caseDetails.getEditActivityUrl;
        $scope.getPrintActivityUrl = caseDetails.getPrintActivityUrl;
      }
    }
  });

  module.controller('civicaseActivityActionsController', civicaseActivityActionsController);

  /**
   * @param {object} $injector injector service
   * @param {object} $scope scope object
   * @param {object} ts ts
   * @param {Array} ActivityActions activity actions
   */
  function civicaseActivityActionsController ($injector, $scope, ts, ActivityActions) {
    $scope.ts = ts;
    $scope.isActionEnabled = isActionEnabled;
    $scope.doAction = doAction;
    $scope.activityActions = ActivityActions;

    (function init () {
      setCustomActions();
    }());

    /**
     * Fetch and set custom actions for the current activity
     */
    function setCustomActions () {
      try {
        $scope.customActionsForActivity = $scope.selectedActivities[0]['api.Activity.getactionlinks'];

        _.each($scope.customActionsForActivity, function (action) {
          action.icon = 'filter_none';
        });
      } catch (e) {}
    }
    /**
     * Get Case Action Service
     *
     * @param {string} actionName name of the action
     * @returns {object/null} action service
     */
    function getActionService (actionName) {
      try {
        return $injector.get(actionName + 'ActivityAction');
      } catch (e) {
        return null;
      }
    }

    /**
     * Check if action is enabled
     *
     * @param {object} action action object
     * @returns {boolean} if action is enabled
     */
    function isActionEnabled (action) {
      var service = getActionService(action.name);
      var isActionEnabledFn = service ? service.isActionEnabled : false;

      if ($scope.isReadOnly && action.isWriteAction) {
        return false;
      } else if (isActionEnabledFn) {
        return isActionEnabledFn($scope);
      } else {
        return true;
      }
    }

    /**
     * Perform Action
     *
     * @param {object} action action object
     */
    function doAction (action) {
      var service = getActionService(action.name);

      if (service) {
        service.doAction($scope, action);
      }
    }
  }
})(CRM.$, CRM._, angular);
