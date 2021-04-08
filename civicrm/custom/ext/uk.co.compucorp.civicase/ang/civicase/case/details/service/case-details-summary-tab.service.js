(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('SummaryCaseTab', SummaryCaseTab);

  /**
   * Summary Case Tab service.
   *
   * @param {object} $location the location service.
   */
  function SummaryCaseTab ($location) {
    /**
     * @returns {string} Returns tab content HTMl template url.
     */
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/summary.html';
    };
  }
})(angular, CRM.$, CRM._);
