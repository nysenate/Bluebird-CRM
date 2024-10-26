(function (angular) {
  var module = angular.module('civicase-base');

  /**
   * Format date filter that can be used on templates.
   */
  module.filter('formatDate', function (DateHelper) {
    return function formatDate (dateString, dateFormat) {
      return DateHelper.formatDate(dateString, dateFormat);
    };
  });
})(angular);
