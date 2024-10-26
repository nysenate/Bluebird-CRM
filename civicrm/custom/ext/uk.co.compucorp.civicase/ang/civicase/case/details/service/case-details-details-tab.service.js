(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('DetailsCaseTab', DetailsCaseTab);

  /**
   * Case Details tab service.
   */
  function DetailsCaseTab () {
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/details.html';
    };
  }
})(angular, CRM.$, CRM._);
