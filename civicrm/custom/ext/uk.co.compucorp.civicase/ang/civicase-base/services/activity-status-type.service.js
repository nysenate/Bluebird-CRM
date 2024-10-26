(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('ActivityStatusType', ActivityStatusType);

  /**
   * Activity Status Type Service
   */
  function ActivityStatusType () {
    this.getAll = function () {
      return CRM['civicase-base'].activityStatusTypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
