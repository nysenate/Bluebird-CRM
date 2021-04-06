(function () {
  var module = angular.module('civicase');

  module.factory('formatActivity', function () {
    return jasmine.createSpy('formatActivity')
      .and.callFake(function (activity) {
        activity.category = [];
        activity.case = {};

        return activity;
      });
  });
})();
