(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseDetailsHeader', function () {
    return {
      replace: true,
      restrict: 'E',
      templateUrl: '~/civicase/case/details/directives/case-details-header.directive.html',
      controller: 'civicaseCaseDetailsHeaderController'
    };
  });

  module.controller('civicaseCaseDetailsHeaderController', civicaseCaseDetailsHeaderController);

  /**
   * @param {object} $scope scope object of the controller
   * @param {object} CaseActions case action service
   * @param {object} WebformsCaseAction webform case action service
   * @param {object} GoToWebformCaseAction go to webform case action service
   * @param {object} webformsList configuration for webforms list
   */
  function civicaseCaseDetailsHeaderController ($scope, CaseActions,
    WebformsCaseAction, GoToWebformCaseAction, webformsList) {
    $scope.webformsAction = CaseActions.findByActionName('Webforms');
    $scope.isGoToWebformAllowed = isGoToWebformAllowed;
    $scope.openWebform = openWebform;
    $scope.webformsListButtonLabel = webformsList.buttonLabel;

    (function init () {
      $scope.$watch('item', updateCaseWebformDropdownVisibility);
      updateCaseWebformDropdownVisibility();
    })();

    /**
     * @param {object} action action object
     */
    function openWebform (action) {
      GoToWebformCaseAction.doAction([$scope.item], action);
    }

    /**
     * @param {object} action action object
     * @returns {boolean} is go to webform action allowed for the sent action
     */
    function isGoToWebformAllowed (action) {
      return GoToWebformCaseAction.isActionAllowed(action, [$scope.item]);
    }

    /**
     * Sets the visibilty of Case Webform Dropdown
     */
    function updateCaseWebformDropdownVisibility () {
      $scope.isCaseWebformDropdownVisible = $scope.webformsAction &&
        WebformsCaseAction.isActionAllowed(
          $scope.webformsAction, [$scope.item], { mode: 'case-details-header' }
        );
    }
  }
})(angular, CRM.$, CRM._);
