(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Ex: <div crm-ui-number-range="model.some_field" />
  module.directive('crmUiNumberRange', function ($timeout) {
    var ts = CRM.ts('civicase');
    return {
      restrict: 'AE',
      scope: {
        data: '=crmUiNumberRange'
      },
      template: '<span><input class="form-control" type="number" ng-model="input.from" placeholder="' + ts('From') + '" /></span>' +
        '<span><input class="form-control" type="number" ng-model="input.to" placeholder="' + ts('To') + '" /></span>',
      link: function (scope, element, attrs) {
        scope.input = {};

        element.addClass('civicase__ui-range');

        // Respond to user interaction with the number widgets
        element.on('change', function () {
          $timeout(function () {
            if (scope.input.from && scope.input.to) {
              scope.data = { BETWEEN: [scope.input.from, scope.input.to] };
            } else if (scope.input.from) {
              scope.data = { '>=': scope.input.from };
            } else if (scope.input.to) {
              scope.data = { '<=': scope.input.to };
            } else {
              scope.data = null;
            }
          });
        });

        scope.$watchCollection('data', function () {
          if (!scope.data) {
            scope.input = {};
          } else if (scope.data.BETWEEN) {
            scope.input.from = scope.data.BETWEEN[0];
            scope.input.to = scope.data.BETWEEN[1];
          } else if (scope.data['>=']) {
            scope.input = { from: scope.data['>='] };
          } else if (scope.data['<=']) {
            scope.input = { to: scope.data['<='] };
          }
        });
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
