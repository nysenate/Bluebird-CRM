(function () {
  var module = angular.module('crmUtil');

  module.factory('crmUiHelp', ['$q', function ($q) {
    var crmUiHelp = jasmine.createSpy('crmUiHelp');

    return crmUiHelp;
  }]);
})();
