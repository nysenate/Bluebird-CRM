(function (angular) {
  var module = angular.module('civicase-base');

  module.directive('civicaseCheckbox', function () {
    return {
      restrict: 'EA',
      controller: 'civicaseCheckboxController',
      templateUrl: '~/civicase-base/directives/checkbox.directive.html',
      transclude: true,
      scope: {
        falseValue: '@civicaseCheckboxFalseValue',
        label: '@civicaseCheckboxLabel',
        ngModel: '=',
        trueValue: '@civicaseCheckboxTrueValue'
      }
    };
  });

  module.controller('civicaseCheckboxController', civicaseCheckboxController);

  /**
   * Checkbox directive's controller.
   *
   * @param {object} $scope the scope object.
   */
  function civicaseCheckboxController ($scope) {
    var trueValue, falseValue;

    $scope.isChecked = false;
    $scope.toggleCheckbox = toggleCheckbox;

    (function init () {
      trueValue = typeof $scope.trueValue !== 'undefined'
        ? $scope.trueValue
        : true;
      falseValue = typeof $scope.falseValue !== 'undefined'
        ? $scope.falseValue
        : false;

      $scope.isChecked = $scope.ngModel === trueValue;
    })();

    /**
     * Toggles the check state of the checkbox and updates the ng model value.
     *
     * @param {object} $event - event object reference.
     */
    function toggleCheckbox ($event) {
      var pressedSpaceOrEnter = $event.type === 'keydown' &&
        ($event.keyCode === 32 || $event.keyCode === 13);

      if ($event.type !== 'click' && !pressedSpaceOrEnter) {
        return;
      }

      $scope.isChecked = !$scope.isChecked;
      var newValue = $scope.isChecked
        ? trueValue
        : falseValue;

      $scope.ngModel = newValue;
      $event.preventDefault();
    }
  }
})(angular);
