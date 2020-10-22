/* eslint-env jasmine */

(function () {
  var module = angular.module('civicase');

  module.factory('formatCase', function () {
    return jasmine.createSpy('formatCase')
      .and.callFake(function (caseObj) {
        return caseObj;
      });
  });
})();
