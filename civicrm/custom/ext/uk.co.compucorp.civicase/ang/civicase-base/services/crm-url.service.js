(function (url, angular) {
  var module = angular.module('civicase-base');

  module.service('civicaseCrmUrl', function () {
    return url;
  });
})(CRM.url, angular);
