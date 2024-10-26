(function(angular, $, _) {
  // Example usage: <crm-mosaico-subject-list crm-mailing="myMailing" />
  angular.module('crmMosaico').directive('crmMosaicoDistribution', function(crmUiHelp) {
    return {
      scope: {
        crmMailing: '@'
      },
      templateUrl: '~/crmMosaico/Distribution.html',
      link: function (scope, elm, attr) {
        scope.$parent.$watch(attr.crmMailing, function(newValue){
          scope.mailing = newValue;
          if (!scope.mailing.template_options.variantsPct) {
            scope.mailing.template_options.variantsPct = CRM.crmMosaico.variantsPct;
          }
        });
        scope.ts = CRM.ts(null);
        scope.hs = crmUiHelp({file: 'CRM/Mailing/MailingUI'});
      }
    };

  });
})(angular, CRM.$, CRM._);
