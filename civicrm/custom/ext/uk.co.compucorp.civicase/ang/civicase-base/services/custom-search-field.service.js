(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('CustomSearchField', CustomSearchField);

  /**
   * CustomSearchField Service
   */
  function CustomSearchField () {
    this.getAll = function () {
      return CRM['civicase-base'].customSearchFields;
    };
  }
})(angular, CRM.$, CRM._, CRM);
