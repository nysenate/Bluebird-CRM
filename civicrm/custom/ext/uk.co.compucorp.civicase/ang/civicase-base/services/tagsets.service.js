(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('Tagset', Tagset);

  /**
   * Tagset Service
   */
  function Tagset () {
    this.getAll = function () {
      return CRM['civicase-base'].tagsets;
    };
  }
})(angular, CRM.$, CRM._, CRM);
