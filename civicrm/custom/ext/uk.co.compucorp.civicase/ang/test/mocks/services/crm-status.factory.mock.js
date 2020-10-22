/* eslint-env jasmine */

(function () {
  var module = angular.module('crmUtil');

  module.factory('crmStatus', ['$q', function ($q) {
    var crmStatus = jasmine.createSpy('crmStatus');

    crmStatus.and.callFake(function (options, promise) {
      return $q.resolve(promise);
    });

    return crmStatus;
  }]);
})();
