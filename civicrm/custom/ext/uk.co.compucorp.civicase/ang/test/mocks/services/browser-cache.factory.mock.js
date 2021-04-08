(function () {
  var module = angular.module('civicase');

  module.factory('BrowserCacheMock', function () {
    return jasmine.createSpyObj('BrowserCache', ['clear', 'get', 'set']);
  });
})();
