(function(angular, $, _) {
  // Example usage: <crm-mosaico-reply-to-list crm-mailing="myMailing" />
  angular.module('crmMosaico').directive('crmMosaicoReplyToList', function(crmUiHelp, crmMosaicoVariants) {
    return {
      scope: {
        crmMailing: '@'
      },
      templateUrl: '~/crmMosaico/ReplyToList.html',
      link: function (scope, elm, attr) {
        scope.$parent.$watch(attr.crmMailing, function(newValue){
          scope.mailing = newValue;
        });
        scope.ts = CRM.ts(null);
        scope.hs = crmUiHelp({file: 'CRM/Mailing/MailingUI'});
        scope.checkPerm = CRM.checkPerm;

        scope.addReplyTo = () => crmMosaicoVariants.split(scope.mailing, 'replyto_email');
        scope.rmReplyTo = (vid) => crmMosaicoVariants.remove(scope.mailing, 'replyto_email', vid);
        scope.isSplit = () => crmMosaicoVariants.isSplit(scope.mailing, 'replyto_email');
        scope.labels = crmMosaicoVariants.getLabels();
      }
    };

  });
})(angular, CRM.$, CRM._);
