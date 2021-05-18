(function (CRM) {
  var module = angular.module('civicase-base');

  module.factory('civicaseCrmUrl', function () {
    var mockURL = CRM.url;

    return mockURL;
  });
})(CRM);
