(function(angular, $, _) {
  // Example usage: <crm-mosaico-subject-list crm-mailing="myMailing" />
  angular.module('crmMosaico').directive('crmMosaicoSubjectList', function(crmUiHelp) {
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

        scope.addSubj = function addSubj() {
          scope.mailing.template_options.variants = [
            {subject: scope.mailing.subject},
            {subject: scope.mailing.subject}
          ]
        };

        scope.rmSubj = function rmSubj(vid) {
          var m = scope.mailing;
          m.template_options.variants.splice(vid, 1);
          if (m.template_options.variants.length === 1) {
            m.subject = m.template_options.variants[0].subject;
            delete m.template_options.variants;
          }
        };

        scope.labels = ['A', 'B'];
      }
    };

  });
})(angular, CRM.$, CRM._);
