(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('FileCategory', FileCategory);

  /**
   * Activity Types Service
   */
  function FileCategory () {
    this.getAll = function () {
      return CRM['civicase-base'].fileCategories;
    };
  }
})(angular, CRM.$, CRM._, CRM);
