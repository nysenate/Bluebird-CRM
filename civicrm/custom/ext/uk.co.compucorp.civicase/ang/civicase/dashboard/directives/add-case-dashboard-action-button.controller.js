(function (angular) {
  var module = angular.module('civicase');

  module.controller('AddCaseDashboardActionButtonController', AddCaseDashboardActionButtonController);

  /**
   * Add Case Dashboard Action Button Controller
   *
   * @param {object} $scope scope object
   * @param {object} $window window object
   * @param {object} ts ts
   * @param {object} AddCase Add Case Service
   * @param {string} currentCaseCategory the current case category name
   */
  function AddCaseDashboardActionButtonController ($scope, $window, ts, AddCase,
    currentCaseCategory) {
    $scope.ts = ts;

    $scope.clickHandler = clickHandler;
    $scope.isVisible = AddCase.isVisible;

    /**
     * Click handler for the Add Case Dashboard button.
     */
    function clickHandler () {
      AddCase.clickHandler({
        callbackFn: redirectToUserContext,
        caseTypeCategoryName: currentCaseCategory
      });
    }

    /**
     * Redirects the user to the user context as provided by the case form
     * response.
     *
     * @param {object} event add case form event reference.
     * @param {object} response add case form response.
     */
    function redirectToUserContext (event, response) {
      var hasNoUserContext = !response.userContext;
      var isCreatingMoreCases = response.buttonName === 'upload_new';

      if (hasNoUserContext || isCreatingMoreCases) {
        return;
      }

      $window.location.href = response.userContext;
    }
  }
})(angular);
