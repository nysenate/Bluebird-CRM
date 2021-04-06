(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Angular binding for civi ajax form events
  module.directive('crmFormSuccess', function () {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        element
          .on('crmFormSuccess', function (event, data) {
            scope.$apply(function () {
              scope.$eval(attrs.crmFormSuccess, {
                $event: event,
                $data: data
              });
            });
          });
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
