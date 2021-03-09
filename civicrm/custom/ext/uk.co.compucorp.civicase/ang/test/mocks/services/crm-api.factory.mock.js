/* eslint-env jasmine */

(function () {
  var module = angular.module('crmUtil');

  module.factory('crmApi', ['$q', function ($q) {
    var crmApi = jasmine.createSpy('crmApi');
    crmApi.and.returnValue($q.resolve());

    return crmApi;
  }]);
})();
