(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('getServiceForInstance', getServiceForInstance);

  /**
   * Get Instance specific Service
   *
   * @param {object} $injector injector service of angular
   * @param {object} pascalCase service to convert a string to pascal case
   * @returns {Function} service
   */
  function getServiceForInstance ($injector, pascalCase) {
    return getServiceForInstance;

    /**
     * Searches for a angularJS service for the current case type category
     * instance, if not found, returns the service for case management service
     * as default.
     *
     * @param {string} instanceName name of the instance
     * @returns {object/null} service
     */
    function getServiceForInstance (instanceName) {
      var CASE_MANAGEMENT_INSTACE_NAME = 'case_management';

      try {
        return $injector.get(
          pascalCase(instanceName) + 'Workflow'
        );
      } catch (e) {
        return $injector.get(
          pascalCase(CASE_MANAGEMENT_INSTACE_NAME) + 'Workflow'
        );
      }
    }
  }
})(angular, CRM.$, CRM._, CRM);
