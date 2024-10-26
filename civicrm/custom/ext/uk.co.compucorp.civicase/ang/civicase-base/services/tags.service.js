(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('Tag', Tag);

  /**
   * Tag Service
   */
  function Tag () {
    this.getAll = function () {
      return CRM['civicase-base'].tags;
    };
  }
})(angular, CRM.$, CRM._, CRM);
