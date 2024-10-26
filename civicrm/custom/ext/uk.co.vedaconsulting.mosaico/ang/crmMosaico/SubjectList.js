(function(angular, $, _) {
  // Example usage: <crm-mosaico-subject-list crm-mailing="myMailing" />
  angular.module('crmMosaico').directive('crmMosaicoSubjectList', function(crmUiHelp, crmMosaicoVariants) {
    return {
      scope: {
        crmMailing: '@'
      },
      templateUrl: '~/crmMosaico/SubjectList.html',
      link: function (scope, elm, attr) {
        scope.$parent.$watch(attr.crmMailing, function(newValue){
          scope.mailing = newValue;
        });
        scope.ts = CRM.ts(null);
        scope.hs = crmUiHelp({file: 'CRM/Mailing/MailingUI'});
        scope.checkPerm = CRM.checkPerm;

        scope.addSubj = () => crmMosaicoVariants.split(scope.mailing, 'subject');
        scope.rmSubj = (vid) => crmMosaicoVariants.remove(scope.mailing, 'subject', vid);
        scope.isSplit = () => crmMosaicoVariants.isSplit(scope.mailing, 'subject');
        scope.labels = crmMosaicoVariants.getLabels();
      }
    };

  });
})(angular, CRM.$, CRM._);
