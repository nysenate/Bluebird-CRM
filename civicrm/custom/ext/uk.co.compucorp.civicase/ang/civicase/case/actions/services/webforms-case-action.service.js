(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('WebformsCaseAction', WebformsCaseAction);

  /**
   * Webforms action for cases.
   *
   * @param {object} $window - window object.
   * @param {object} GoToWebformCaseAction - GoToWebformCaseAction object.
   * @param {object} webformsList - configuration for webforms list.
   */
  function WebformsCaseAction ($window, GoToWebformCaseAction, webformsList) {
    this.isActionAllowed = isActionAllowed;

    /**
     * Check if action is allowed.
     *
     * @param {object} action - action data.
     * @param {object} cases - cases.
     * @param {object} attributes - item attributes.
     *
     * @returns {boolean} - true if action is allowed, false otherwise.
     */
    function isActionAllowed (action, cases, attributes) {
      if (attributes && attributes.mode === 'case-details') {
        return checkIfWebformsExist(action.items, cases[0].case_type_id) && !webformsList.isVisible;
      } else if (attributes && attributes.mode === 'case-details-header') {
        return checkIfWebformsExist(action.items, cases[0].case_type_id) && webformsList.isVisible;
      }
    }

    /**
     * Checks if any webforms are available for the sent case type id
     *
     * @param {Array} webforms list of webforms
     * @param {string} caseTypeID case type id
     * @returns {boolean} if any webforms are available for the sent case type id
     */
    function checkIfWebformsExist (webforms, caseTypeID) {
      return !!_.find(webforms, function (webform) {
        return GoToWebformCaseAction.checkIfWebformVisible(webform, caseTypeID);
      });
    }
  }
})(angular, CRM.$, CRM._);
