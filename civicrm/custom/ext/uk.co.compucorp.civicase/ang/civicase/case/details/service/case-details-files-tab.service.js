(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('FilesCaseTab', FilesCaseTab);

  /**
   * Files Case Tab service.
   *
   * @param {object} $location the location service.
   */
  function FilesCaseTab ($location) {
    /**
     * @returns {string} Returns tab content HTMl template url.
     */
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/files.html';
    };
  }
})(angular, CRM.$, CRM._);
