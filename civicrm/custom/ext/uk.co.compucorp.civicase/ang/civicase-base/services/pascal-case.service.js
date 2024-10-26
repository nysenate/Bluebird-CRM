(function (angular, _, crmTs) {
  var module = angular.module('civicase-base');

  module.service('pascalCase', function () {
    /**
     * @param {string} string string to capitalize
     * @returns {string} capitalized string
     */
    return function pascalCase (string) {
      return _.chain(string).camelCase().capitalize().value();
    };
  });
})(angular, CRM._, window.ts);
