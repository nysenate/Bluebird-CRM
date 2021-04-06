(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('DeleteCasesCaseAction', DeleteCasesCaseAction);

  /**
   * Delete case action service
   */
  function DeleteCasesCaseAction () {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases list of cases
     * @param {object} action action object
     * @param {Function} callbackFn callback function
     */
    this.doAction = function (cases, action, callbackFn) {
      var ts = CRM.ts('civicase');
      var msg;
      var trash = 1;

      switch (action.type) {
        case 'delete':
          trash = 0;
          msg = cases.length === 1 ? ts('Permanently delete selected case? This cannot be undone.') : ts('Permanently delete %1 cases? This cannot be undone.', { 1: cases.length });
          break;

        case 'restore':
          msg = cases.length === 1 ? ts('Undelete selected case?') : ts('Undelete %1 cases?', { 1: cases.length });
          break;

        default:
          msg = cases.length === 1 ? ts('This case and all associated activities will be moved to the trash.') : ts('%1 cases and all associated activities will be moved to the trash.', { 1: cases.length });
          action.type = 'delete';
      }

      CRM.confirm({ title: action.title, message: msg })
        .on('crmConfirm:yes', function () {
          var calls = [];

          _.each(cases, function (item) {
            calls.push(['Case', action.type, { id: item.id, move_to_trash: trash }]);
          });

          callbackFn(calls);
        });
    };
  }
})(angular, CRM.$, CRM._);
