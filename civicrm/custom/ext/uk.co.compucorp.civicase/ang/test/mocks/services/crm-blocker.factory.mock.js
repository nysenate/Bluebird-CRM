(function () {
  var module = angular.module('crmUtil');

  module.factory('crmBlocker', function () {
    var crmBlocker = jasmine.createSpy('crmBlocker');

    crmBlocker.and.callFake(function (callable) {
      if (callable) {
        return callable();
      }
    });

    return crmBlocker;
  });
})();
