(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Ensures that this value is removed from the model when the field is removed via ng-if
  module.directive('crmUiConditional', function () {
    return {
      restrict: 'A',
      link: function (scope, elem, attrs) {
        scope.$on('$destroy', function () {
          var modelAttr = attrs.ngModel || attrs.crmUiDateRange;
          var val = scope.$eval(modelAttr);
          if (typeof val !== 'undefined') {
            scope.$eval(modelAttr + ' = null');
          }
        });
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
