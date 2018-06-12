(function (angular, $, _) {
  angular.module('uibTabsetClass', CRM.angRequires('uibTabsetClass'));
  angular.module('uibTabsetClass').directive('uibTabsetClass', function ($timeout) {
    return {
      link: function (scope, element, attrs) {
        $timeout(function () {
          element.find('.nav').first().addClass(attrs.uibTabsetClass);
        });
      }
    };
  });
})(angular, CRM.$, CRM._);
