(function(angular, $, _) {

  function activityCard($scope, getActivityFeedUrl, dialogService, templateExists) {
    var ts = $scope.ts = CRM.ts('civicase');
    $scope.CRM = CRM;
    $scope.activityFeedUrl = getActivityFeedUrl;
    $scope.templateExists = templateExists;

    $scope.isActivityEditable = function(activity) {
      var type = CRM.civicase.activityTypes[activity.activity_type_id].name;
      return (type !== 'Email' && type !== 'Print PDF Letter') && $scope.editActivityUrl;
    };

    $scope.markCompleted = function(act) {
      $scope.refresh([['Activity', 'create', {id: act.id, status_id: act.is_completed ? 'Scheduled' : 'Completed'}]]);
    };

    $scope.star = function(act) {
      act.is_star = act.is_star === '1' ? '0' : '1';
      // Setvalue api avoids messy revisioning issues
      $scope.refresh([['Activity', 'setvalue', {id: act.id, field: 'is_star', value: act.is_star}]]);
    };

    $scope.deleteActivity = function(activity, dialog) {
      CRM.confirm({
          title: ts('Delete Activity'),
          message: ts('Permanently delete this %1 activity?', {1: activity.type})
        })
        .on('crmConfirm:yes', function() {
          $scope.refresh([['Activity', 'delete', {id: activity.id}]]);
          if (dialog && $(dialog).data('uiDialog')) {
            $(dialog).dialog('close');
          }
        });
    };

    $scope.viewInPopup = function($event, activity) {
      if (!$event || !$($event.target).is('a, a *, input, button, button *')) {
        var context = activity.case_id ? 'case' : 'activity';
        var form = CRM.loadForm(CRM.url('civicrm/activity', {action: 'view', id: activity.id, reset: 1, context: context}))
          .on('crmFormSuccess', function() {
            $scope.refresh();
          })
          .on('crmLoad', function() {
            $('a.delete.button').click(function() {
              $scope.deleteActivity(activity, form);
              return false;
            });
          });
      }
    };

    $scope.moveCopyActivity = function(act, op) {
      var model = {
        ts: ts,
        activity: _.cloneDeep(act)
      };
      dialogService.open('MoveCopyActCard', '~/civicase/ActivityMoveCopy.html', model, {
        autoOpen: false,
        height: 'auto',
        width: '40%',
        title: op === 'move' ? ts('Move %1 Activity', {1: act.type}) : ts('Copy %1 Activity', {1: act.type}),
        buttons: [{
          text: ts('Save'),
          icons: {primary: 'fa-check'},
          click: function() {
            if (op === 'copy') {
              delete model.activity.id;
            }
            if (model.activity.case_id && model.activity.case_id != act.case_id) {
              $scope.refresh([['Activity', 'create', model.activity]]);
            }
            $(this).dialog('close');
          }
        }]
      });
    };

    $scope.getAttachments = function(activity) {
      if (!activity.attachments) {
        activity.attachments = [];
        CRM.api3('Attachment', 'get', {
          entity_table: 'civicrm_activity',
          entity_id: activity.id,
          sequential: 1
        }).done(function(data) {
          activity.attachments = data.values;
          $scope.$digest();
        });
      }
    };
  }

  angular.module('civicase').directive('caseActivityCard', function() {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/ActivityCard.html',
      controller: activityCard,
      scope: {
        activity: '=caseActivityCard',
        refresh: '=refreshCallback',
        editActivityUrl: '=?editActivityUrl'
      }
    };
  });

})(angular, CRM.$, CRM._);
