(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('GoToWebformCaseAction', GoToWebformCaseAction);

  /**
   * @param {object} $window browsers window object
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function GoToWebformCaseAction ($window, civicaseCrmUrl) {
    this.doAction = doAction;
    this.isActionAllowed = isActionAllowed;
    this.checkIfWebformVisible = checkIfWebformVisible;

    /**
     * Click event handler for the Action
     *
     * @param {Array} cases cases object
     * @param {object} action action object
     * @param {Function} callbackFn callback function
     */
    function doAction (cases, action, callbackFn) {
      var window;
      var urlObject = { case1: cases[0].id };

      if (action.clientID) {
        urlObject['cid' + action.clientID] = cases[0].client[0].contact_id;
      }

      CRM.alert(
        ts('Please refresh this page to view updates from the webform submission.'),
        ts('Refresh'),
        'info',
        { expires: 1800000 }
      );

      window = $window.open(civicaseCrmUrl(action.path, urlObject), '_blank');
      window.focus();
    }

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
      return checkIfWebformVisible(action, cases[0].case_type_id);
    }

    /**
     * @param {object} webform webform action object
     * @param {string} caseTypeID case type id
     * @returns {boolean} if sent webform is visible for sent case type id
     */
    function checkIfWebformVisible (webform, caseTypeID) {
      return webform.case_type_ids.length === 0 ||
        webform.case_type_ids.indexOf(caseTypeID) !== -1;
    }
  }
})(angular, CRM.$, CRM._);
