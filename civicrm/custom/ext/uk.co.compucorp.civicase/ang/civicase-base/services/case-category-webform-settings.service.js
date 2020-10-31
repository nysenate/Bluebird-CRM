(function (angular, _, configuration) {
  var module = angular.module('civicase-base');

  module.service('CaseCategoryWebformSettings', CaseCategoryWebformSettings);

  /**
   * CaseCategoryWebformSettings Service
   */
  function CaseCategoryWebformSettings () {
    var caseCategoryWebformSettings = configuration.caseCategoryWebformSettings;

    this.getSettingsFor = getSettingsFor;

    /**
     * @param {string} caseCategoryName name of the case category
     * @returns {object} settings for given case category
     */
    function getSettingsFor (caseCategoryName) {
      return caseCategoryWebformSettings[caseCategoryName.toLowerCase()];
    }
  }
})(angular, CRM._, CRM['civicase-base']);
