(function(angular, $, _) {

  // Controller for editing mail-content with Mosaico in a popup dialog
  // Scope members:
  //   - [input] "model": Object
  //     - "mailing": Object, CiviMail mailing
  //     - "attachments": Object, CrmAttachment
  angular.module('crmMosaico').controller('CrmMosaicoAdvancedDialogCtrl', function CrmMosaicoAdvancedDialogCtrl($scope, dialogService) {
    var ts = $scope.ts = CRM.ts(null);
  });

})(angular, CRM.$, CRM._);
