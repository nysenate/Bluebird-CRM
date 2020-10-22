(function (angular) {
  var module = angular.module('civicase');

  module.config(function ($provide) {
    $provide.decorator('uibDatepickerDirective', function ($controller, $delegate) {
      var datepicker = $delegate[0];

      datepicker.compile = function () {
        return function ($scope) {
          datepicker.link.apply(this, arguments);

          // Watches for changes in the value of the currently selected date
          // and emits an event if the month has changed
          $scope.$watch('activeDt.date', function (newDate, oldDate) {
            if (newDate && oldDate && (newDate.getMonth() !== oldDate.getMonth())) {
              $scope.$emit('civicase::uibDaypicker::monthSelected', $scope.activeDt.date);
            }
          });
        };
      };

      return $delegate;
    });
  });
})(angular);
