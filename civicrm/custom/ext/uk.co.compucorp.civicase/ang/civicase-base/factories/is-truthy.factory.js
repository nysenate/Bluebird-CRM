(function (angular, _, CRM) {
  var module = angular.module('civicase-base');

  module.factory('isTruthy', function () {
    return isTruthy;
  });

  /**
   * Checks if the sent value is truthy or not.
   * This is useful because civicrm backend returns truthy values as '1'
   * and falsy values as '0'
   *
   * @param {boolean/number/string} value the value as provided by Select2.
   * @returns {Array} value
   */
  function isTruthy (value) {
    if (_.isString(value)) {
      return value === '1';
    } else {
      return !!value;
    }
  }
})(angular, CRM._, CRM);
