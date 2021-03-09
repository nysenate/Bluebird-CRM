(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Ex: <div civicase-ui-date-range="model.some_field" />
  module.directive('civicaseUiDateRange', function ($timeout) {
    return {
      restrict: 'E',
      replace: true,
      scope: {
        data: '=dateRange',
        onChange: '&'
      },
      templateUrl: '~/civicase/shared/directives/ui-date-range.directive.html',
      controller: 'civicaseUiDateRangeController',
      link: civicaseUiDateRangeLink
    };

    /**
     * Link function for civicaseUiDateRange directive
     *
     * Given that the directive uses crm-ui-datepicker with `time: false`
     * (that is, the user can't select the time manually), it makes sure that,
     * if the `enforce-time` attribute is applied, any selected "from" date is
     * set with the time = 00:00:00 and any selected "to" date with the time = 23:59:59
     *
     * @param {object} $scope Scope object reference.
     * @param {object} element Directive element reference.
     * @param {object} attrs Element attributes map.
     */
    function civicaseUiDateRangeLink ($scope, element, attrs) {
      var enforceTime = Object.prototype.hasOwnProperty.call(attrs, 'enforceTime');

      // Respond to user interaction with the date widgets
      element.on('change', function (e, context) {
        if (context === 'userInput' || context === 'crmClear') {
          $timeout(function () {
            if ($scope.input.from && $scope.input.to) {
              $scope.data = {
                BETWEEN: [
                  setAsRangeLimit($scope.input.from, 'lower'),
                  setAsRangeLimit($scope.input.to, 'upper')
                ]
              };
            } else if ($scope.input.from) {
              $scope.data = { '>=': setAsRangeLimit($scope.input.from, 'lower') };
            } else if ($scope.input.to) {
              $scope.data = { '<=': setAsRangeLimit($scope.input.to, 'upper') };
            } else {
              $scope.data = null;
            }
          });
        }
      });

      /**
       * Given a date or datetime, it returns it as the lower or upper (depending
       * on the value of the `limit` argument) date range limit
       *
       * If the directive didn't have the `enforce-time` attribute applied, then
       * it will simply return the original value
       *
       * @param {string} dateTime
       *   could be either YYYY-MM-DD or YYYY-MM-DD HH:mm:ss
       * @param {string} [limit="lower"]
       *   whether the datetime should be set as the lower or upper limit
       * @returns {string} A date string
       */
      function setAsRangeLimit (dateTime, limit) {
        var date;

        limit = limit !== 'upper' ? 'lower' : limit;
        date = dateTime.split(' ')[0];

        return !enforceTime
          ? date
          : date + ' ' + (limit === 'lower' ? '00:00:00' : '23:59:59');
      }
    }
  });

  /**
   * Controller for civicaseUiDateRange directive
   */
  module.controller('civicaseUiDateRangeController', function ($scope) {
    $scope.input = {};

    $scope.$watchCollection('data', function () {
      if (!$scope.data) {
        $scope.input = {};
      } else if ($scope.data.BETWEEN) {
        $scope.input.from = $scope.data.BETWEEN[0];
        $scope.input.to = $scope.data.BETWEEN[1];
      } else if ($scope.data['>=']) {
        $scope.input = { from: $scope.data['>='] };
      } else if ($scope.data['<=']) {
        $scope.input = { to: $scope.data['<='] };
      }

      $scope.onChange();
    });
  });
})(angular, CRM.$, CRM._, CRM);
