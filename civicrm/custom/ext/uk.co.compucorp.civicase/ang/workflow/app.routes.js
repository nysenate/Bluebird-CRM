(function (angular, $, _) {
  var module = angular.module('workflow');

  module.config(function ($routeProvider, UrlParametersProvider) {
    $routeProvider.when('/list', {
      template: function () {
        var urlParams = UrlParametersProvider.parse(window.location.search);

        return '<workflow-list case-type-category="' + urlParams.case_type_category + '"></workflow-list>';
      }
    });
  });
})(angular, CRM.$, CRM._);
