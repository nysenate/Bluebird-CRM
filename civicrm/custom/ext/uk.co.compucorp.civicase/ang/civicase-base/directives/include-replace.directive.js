(function (angular, $, _) {
  var module = angular.module('civicase-base');

  /**
   * The `ngInclude` directive always creates an extra html element,
   * but sometimes we need the template to replace the ng-include element.
   * This directive can be used in such cases.
   */
  module.directive('civicaseIncludeReplace', function () {
    return {
      require: 'ngInclude',
      link: function (scope, element, attrs) {
        element.replaceWith(element.children());
      }
    };
  });
})(angular, CRM.$, CRM._);
