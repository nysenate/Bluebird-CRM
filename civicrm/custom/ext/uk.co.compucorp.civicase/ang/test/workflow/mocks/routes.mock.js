((angular) => {
  const module = angular.module('workflow.mock');

  module.config(($routeProvider) => {
    $routeProvider.when('/caseType/:id', {
      template: ''
    });
  });
})(angular);
