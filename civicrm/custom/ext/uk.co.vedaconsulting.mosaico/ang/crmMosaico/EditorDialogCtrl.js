(function(angular, $, _) {

  // Controller for editing mail-content with Mosaico in a popup dialog
  // Scope members:
  //   - [input] "model": Object
  //     - "url": string
  angular.module('crmMosaico').controller('CrmMosaicoEditorDialogCtrl', function CrmMosaicoEditorDialogCtrl($scope, dialogService) {
    var ts = $scope.ts = CRM.ts(null);
  });

})(angular, CRM.$, CRM._);
