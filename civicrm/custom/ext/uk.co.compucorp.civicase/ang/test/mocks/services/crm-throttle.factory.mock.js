(function () {
  var module = angular.module('crmUtil');

  module.factory('crmThrottle', function () {
    var crmThrottle = jasmine.createSpy('crmThrottle');

    crmThrottle.and.callFake(function (callable) {
      return callable();
    });

    return crmThrottle;
  });
})();
