(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.factory('getSelect2Value', function () {
    return getSelect2Value;
  });

  /**
   * Returns Select2 values as arrays. Select2 returns a single selected value
   * as an array, but multiple values as a string separated by comas.
   *
   * @param {Array|string} value the value as provided by Select2.
   * @returns {Array} value
   */
  function getSelect2Value (value) {
    if (value) {
      return _.isArray(value)
        ? value
        : value.split(',');
    } else {
      return [];
    }
  }
})(angular, CRM.$, CRM._, CRM);
