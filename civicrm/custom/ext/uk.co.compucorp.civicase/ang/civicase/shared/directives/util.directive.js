(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Export a set of civicase-related utility functions.
  // <div civicase-util="myhelper" />
  module.directive('civicaseUtil', function () {
    return {
      restrict: 'EA',
      scope: {
        civicaseUtil: '='
      },
      controller: function ($scope, formatActivity) {
        var util = this;
        util.formatActivity = function (a) { formatActivity(a); return a; };
        util.formatActivities = function (rows) { _.each(rows, formatActivity); return rows; };
        util.isSameDate = function (d1, d2) {
          return d1 && d2 && (d1.slice(0, 10) === d2.slice(0, 10));
        };

        $scope.civicaseUtil = this;
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
