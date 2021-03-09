(function (loadForm, angular) {
  var module = angular.module('civicase-base');

  module.service('civicaseCrmLoadForm', function () {
    return loadForm;
  });
})(CRM.loadForm, angular);
