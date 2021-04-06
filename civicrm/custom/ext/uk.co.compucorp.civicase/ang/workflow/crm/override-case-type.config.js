(function (angular) {
  var module = angular.module('workflow');

  module.config(function ($provide) {
    $provide.decorator('$route', function ($delegate) {
      // Changing the controller of the following route to override the core
      // functionality
      var caseTypeEditRoute = $delegate.routes['/caseType/:id'];
      caseTypeEditRoute.controller = 'CivicaseCaseTypeController';

      return $delegate;
    });
  });
})(angular);
