(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('ActivitiesCaseTab', ActivitiesCaseTab);

  /**
   * Activities Case Tab service.
   *
   * @param {object} $location the location service.
   * @param {Function} crmApi the CRM API service.
   */
  function ActivitiesCaseTab ($location, crmApi) {
    /**
     * @returns {string} Returns tab content HTMl template url.
     */
    this.activeTabContentUrl = function () {
      return '~/civicase/case/details/directives/tab-content/activity.html';
    };
  }
})(angular, CRM.$, CRM._);
