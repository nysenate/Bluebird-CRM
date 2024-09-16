(function(angular, $, _) {

  // Controller for previewing mail-content from Mosaico
  // Scope members:
  //   - [input] "model": Object
  //     - "mailing": Object, CiviMail mailing
  //     - "attachments": Object, CrmAttachment
  angular.module('crmMosaico').controller('CrmMosaicoPreviewDialogCtrl', function CrmMosaicoPreviewDialogCtrl($scope, crmMailingMgr, crmMailingPreviewMgr, crmBlocker, crmStatus, crmMosaicoVariants) {
    var ts = $scope.ts = CRM.ts(null);
    var block = $scope.block = crmBlocker();

    // @return Promise
    $scope.previewMailing = function previewMailing(mailing, variantId, mode) {
      const preview = crmMosaicoVariants.preview(mailing, variantId);
      return crmMailingPreviewMgr.preview(preview, mode);
    };

    // @return Promise
    $scope.sendTest = function sendTest(mailing, variantId, attachments, recipient) {
      var savePromise = crmMailingMgr.save(mailing)
        .then(function() {
          return attachments.save();
        });
      return block(crmStatus({start: ts('Saving...'), success: ''}, savePromise)
        .then(function() {
          crmMailingPreviewMgr.sendTest(mailing, recipient);
        }));
    };
  });

})(angular, CRM.$, CRM._);
