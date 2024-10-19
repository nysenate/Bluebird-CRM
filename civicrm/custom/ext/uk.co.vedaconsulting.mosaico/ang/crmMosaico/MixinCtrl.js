(function(angular, $, _) {

  // This provides additional actions for editing a Mosaico mailing.
  // It coexists with crmMailing's EditMailingCtrl.
  angular.module('crmMosaico').controller('CrmMosaicoMixinCtrl', function CrmMosaicoMixinCtrl($scope, dialogService, crmMosaicoVariants) {
    const ts = $scope.ts = CRM.ts('mosaico');

    const singleDesign = [
      {title: ts('Design'), vid: null, action: 'split'}
    ];
    const abDesign = [
      {title: ts('Design (A)'), vid: 0, action: 'unsplit'},
      {title: ts('Design (B)'), vid: 1, action: 'unsplit'},
    ];

    $scope.getDesigns = function(mailing) {
      if (!mailing) return [];
      return crmMosaicoVariants.isSplit(mailing, 'body_html') ? abDesign : singleDesign;
    }
    $scope.unsplitDesign = function(mailing, vid) {
      crmMosaicoVariants.remove(mailing, ['mosaicoTemplate', 'mosaicoMetadata', 'mosaicoContent', 'body_html'], vid);
    };
    $scope.splitDesign = function(mailing) {
      crmMosaicoVariants.split(mailing, ['mosaicoTemplate', 'mosaicoMetadata', 'mosaicoContent', 'body_html']);
    };

    $scope.isMailingSplit = (mailing, field) => crmMosaicoVariants.isSplit(mailing, field);

    var activeDialogs = {};

    // Open a dialog of advanced options.
    $scope.openAdvancedOptions = function() {
      var model = {mailing: $scope.mailing, attachments: $scope.attachments};
      var options = CRM.utils.adjustDialogDefaults(angular.extend(
        {
          autoOpen: false,
          title: ts('Advanced Settings'),
          width: 600,
          height: 'auto'
        },
        options
      ));
      activeDialogs.crmMosaicoAdvancedDialog = 1;
      return dialogService.open('crmMosaicoAdvancedDialog', '~/crmMosaico/AdvancedDialogCtrl.html', model, options)
        .finally(function(){ delete activeDialogs.crmMosaicoAdvancedDialog; });
    };

    $scope.$on("$destroy", function() {
      angular.forEach(activeDialogs, function(v,name){
        dialogService.cancel(name);
      });
    });

  });

})(angular, CRM.$, CRM._);
