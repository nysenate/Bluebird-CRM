(function(angular, $, _) {
  // Example usage: <crm-mosaico-from-list crm-mailing="myMailing" />
  angular.module('crmMosaico').directive('crmMosaicoFromList', function(crmUiHelp, crmMosaicoVariants) {
    return {
      scope: {
        crmMailing: '@'
      },
      templateUrl: '~/crmMosaico/FromList.html',
      link: function (scope, elm, attr) {
        scope.$parent.$watch(attr.crmMailing, function(newValue){
          scope.mailing = newValue;
        });
        scope.ts = CRM.ts(null);
        scope.hs = crmUiHelp({file: 'CRM/Mailing/MailingUI'});
        scope.checkPerm = CRM.checkPerm;

        scope.addFrom = function() {
          crmMosaicoVariants.split(scope.mailing, 'from_name');
          crmMosaicoVariants.split(scope.mailing, 'from_email');
          if (!CRM.crmMailing.enableReplyTo) {
            // Ugh. Brain hurts. See also: crmMailingFromAddress (CRM-18364 behavior)
            crmMosaicoVariants.split(scope.mailing, 'replyto_email');
          }

        }
        scope.rmFrom = function(vid) {
          crmMosaicoVariants.remove(scope.mailing, 'from_email', vid);
          crmMosaicoVariants.remove(scope.mailing, 'from_name', vid);
          if (!CRM.crmMailing.enableReplyTo) {
            // Ugh. Brain hurts. See also: crmMailingFromAddress (CRM-18364 behavior)
            crmMosaicoVariants.remove(scope.mailing, 'replyto_email', vid);
          }
        }
        scope.isSplit = function() {
          return crmMosaicoVariants.isSplit(scope.mailing, 'from_email')
        }
        scope.labels = crmMosaicoVariants.getLabels();
      }
    };

  });
})(angular, CRM.$, CRM._);
