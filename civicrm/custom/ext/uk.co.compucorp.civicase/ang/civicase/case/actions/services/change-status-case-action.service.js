(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('ChangeStatusCaseAction', ChangeStatusCaseAction);

  /**
   * Change Status Case Action
   *
   * @param {object} CaseStatus case status service
   * @param {object} CaseType case type service
   */
  function ChangeStatusCaseAction (CaseStatus, CaseType) {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases cases
     * @param {object} action action
     * @param {Function} callbackFn callback function
     */
    this.doAction = function (cases, action, callbackFn) {
      var ts = CRM.ts('civicase');
      var types = _.uniq(_.map(cases, 'case_type_id'));
      var currentStatuses = _.uniq(_.collect(cases, 'status_id'));
      var currentStatus = currentStatuses.length === 1 ? currentStatuses[0] : null;
      var msg = '<form>' +
          '<div><input name="change_case_status" placeholder="' +
          ts('Select New Status') + '" /></div>' +
          '<label for="change_case_status_details">' + ts('Notes') + '</label>' +
          '<textarea id="change_case_status_details"></textarea>' +
          '</form>';
      var statuses = _.map(CaseStatus.getAll(), function (item, statusId) {
        return {
          id: item.name,
          text: item.label,
          disabled: statusId === currentStatus
        };
      });

      _.each(types, function (caseTypeId) {
        var statuses = CaseType.getById(caseTypeId).definition.statuses;
        var allowedStatuses = statuses || [];

        if (allowedStatuses.length) {
          _.remove(statuses, function (status) {
            return allowedStatuses.indexOf(status.id) < 0;
          });
        }
      });

      var dialog = CRM.confirm({
        title: action.title,
        message: msg,
        open: function () {
          $('input[name=change_case_status]', this).crmSelect2({ data: statuses });
          CRM.wysiwyg.create('#change_case_status_details').then(function () {
            alignDialogBoxCenter(dialog);
          });
        }
      }).on('crmConfirm:yes', function () {
        var status = $('input[name=change_case_status]', this).val();
        var details = $('#change_case_status_details').val();
        var calls = [];

        if (status) {
          _.each(cases, function (item) {
            var subject = ts('Case status changed from %1 to %2', {
              1: item.status,
              2: _.result(_.find(statuses, { id: status }), 'text')
            });

            calls.push(['Case', 'create', { id: item.id, status_id: status }]);
            calls.push(['Activity', 'create', { case_id: item.id, status_id: 'Completed', activity_type_id: 'Change Case Status', subject: subject, details: details }]);
          });

          callbackFn(calls);
        }
      });

      /**
       * Aligns the dialog box center to the screen
       *
       * @param {object} dialog box to be aligned center
       */
      function alignDialogBoxCenter (dialog) {
        if (dialog && dialog.data('uiDialog')) {
          dialog.parent().position({ my: 'center', at: 'center', of: window });
        }
      }
    };
  }
})(angular, CRM.$, CRM._);
