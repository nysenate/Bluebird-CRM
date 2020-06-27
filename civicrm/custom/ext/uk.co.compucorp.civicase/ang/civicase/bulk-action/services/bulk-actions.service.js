(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.service('BulkActions', BulkActionsService);

  function BulkActionsService () {
    /**
     * Checks if bulkactions are available
     *
     * @return {Boolean}
     */
    this.isAllowed = function () {
      if (CRM.checkPerm('basic case information') &&
      !CRM.checkPerm('administer CiviCase') &&
      !CRM.checkPerm('access my cases and activities') &&
      !CRM.checkPerm('access all cases and activities')
      ) {
        return false;
      } else {
        return true;
      }
    };
  }
})(angular, CRM.$, CRM._, CRM);
