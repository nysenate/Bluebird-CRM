(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('LinkedCasesCaseTab', LinkedCasesCaseTab);

  /**
   * Linked Case Tab service.
   */
  function LinkedCasesCaseTab () {
    /**
     * @returns {string} Returns tab content HTMl template url.
     */
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/linked-cases.html';
    };
  }
})(angular, CRM.$, CRM._);
