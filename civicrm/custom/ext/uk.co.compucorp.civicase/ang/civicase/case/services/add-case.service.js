(function (_, angular, checkPerm, loadForm) {
  var module = angular.module('civicase');

  module.service('AddCase', AddCaseService);

  /**
   * Add Case Service
   *
   * @param {object} $window the window service.
   * @param {string} CaseCategoryWebformSettings service to fetch case category webform settings
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function AddCaseService ($window, CaseCategoryWebformSettings, civicaseCrmUrl) {
    this.clickHandler = clickHandler;
    this.isVisible = isVisible;

    /**
     * Displays a form to add a new case. If a custom "Add Case" webform url has been configured,
     * it will redirect to it. Otherwise it will open a CRM form popup to add a new case.
     *
     * @param {addCaseConfig} params parameters
     */
    function clickHandler (params) {
      var webformSettings = CaseCategoryWebformSettings.getSettingsFor(params.caseTypeCategoryName);
      var hasCustomNewCaseWebformUrl = !!webformSettings.newCaseWebformUrl;

      hasCustomNewCaseWebformUrl
        ? redirectToCustomNewCaseWebformUrl(webformSettings, params.contactId)
        : openNewCaseForm(params);
    }

    /**
     * Will display the button if the user can add cases.
     *
     * @returns {boolean} returns true when the user can add cases.
     */
    function isVisible () {
      var canAddCases = checkPerm('add cases');

      return canAddCases;
    }

    /**
     * Opens a new CRM form popup to add new cases. If a case type category was defined we
     * use it to limit the type of cases that can be created by this category.
     *
     * @param {addCaseConfig} params parameters
     */
    function openNewCaseForm (params) {
      var formParams = {
        action: 'add',
        case_type_category: params.caseTypeCategoryName,
        context: 'standalone',
        reset: 1
      };

      if (params.contactId) {
        formParams.civicase_cid = params.contactId;
      }

      var formUrl = civicaseCrmUrl('civicrm/case/add', formParams);

      loadForm(formUrl)
        .on('crmFormSuccess crmPopupFormSuccess', params.callbackFn);
    }

    /**
     * Redirects the user to the custom webform URL as defined in the configuration.
     *
     * @param {string} webformSettings web form settings
     * @param {string} contactId contact id
     */
    function redirectToCustomNewCaseWebformUrl (webformSettings, contactId) {
      var url = webformSettings.newCaseWebformUrl;

      if (contactId) {
        url += '?' + webformSettings.newCaseWebformClient + '=' + contactId;
      }

      $window.location.href = url;
    }

    /**
     * @typedef {object} addCaseConfig
     * @property {string} caseTypeCategoryName the case category name
     * @property {number} contactId contact id
     * @property {number} callbackFn callback function
     */
  }
})(CRM._, angular, CRM.checkPerm, CRM.loadForm);
