(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('CustomActivityField', CustomActivityField);

  /**
   * Tagset Service
   */
  function CustomActivityField () {
    this.getAll = function () {
      return CRM['civicase-base'].customActivityFields;
    };
  }
})(angular, CRM.$, CRM._, CRM);
