(function (_) {
  var module = angular.module('crmUi');

  module.directive('crmUiSelect', function () {
    return {
      require: '?ngModel',
      priority: 1,
      scope: {
        crmUiSelect: '='
      },
      link: _.noop
    };
  });
})(CRM._);
