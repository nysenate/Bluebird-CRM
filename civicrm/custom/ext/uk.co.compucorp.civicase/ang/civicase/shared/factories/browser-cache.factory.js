(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('BrowserCache', function () {
    return CRM.cache;
  });
})(angular, CRM.$, CRM._, CRM);
