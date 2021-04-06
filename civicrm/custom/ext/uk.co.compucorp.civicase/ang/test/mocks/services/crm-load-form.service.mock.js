/* eslint-env jasmine */

(function (CRM) {
  var module = angular.module('civicase-base');

  module.factory('civicaseCrmLoadForm', function () {
    var mockLoadForm = CRM.loadForm;

    mockLoadForm.and.returnValue({
      one: jasmine.createSpy(),
      on: jasmine.createSpy()
    });
    return mockLoadForm;
  });
})(CRM);
