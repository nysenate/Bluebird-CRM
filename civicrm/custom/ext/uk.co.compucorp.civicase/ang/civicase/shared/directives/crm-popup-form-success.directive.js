(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Angular binding for crm-popup links
  module.directive('crmPopupFormSuccess', function () {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        element.addClass('crm-popup')
          .on('crmPopupFormSuccess', function (event, element, data) {
            scope.$apply(function () {
              scope.$eval(attrs.crmPopupFormSuccess, { $event: event, $data: data });
            });
          });
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
