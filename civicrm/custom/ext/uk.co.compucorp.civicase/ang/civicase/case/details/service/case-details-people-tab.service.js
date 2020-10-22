(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('PeopleCaseTab', PeopleCaseTab);

  /**
   * People Case Tab service.
   *
   * @param {object} $location the location service.
   * @param {Function} crmApi the CRM API service.
   */
  function PeopleCaseTab ($location, crmApi) {
    /**
     * @returns {string} Returns tab content HTMl template url.
     */
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/people.html';
    };
  }
})(angular, CRM.$, CRM._);
