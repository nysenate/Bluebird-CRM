/* eslint-env jasmine */

(function (CRM) {
  var module = angular.module('civicase-base');

  module.factory('civicaseCrmUrl', function () {
    return CRM.url;
  });
})(CRM);
