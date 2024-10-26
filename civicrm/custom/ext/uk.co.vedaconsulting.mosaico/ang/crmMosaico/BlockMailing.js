(function(angular, $, _) {
  angular.module('crmMosaico').directive('crmMosaicoBlockMailing', function(crmMailingSimpleDirective, crmMosaicoVariants) {
    const d = crmMailingSimpleDirective('crmMosaicoBlockMailing', '~/crmMosaico/BlockMailing.html');
    const link = d.link;
    d.link = function(scope, elm, attr) {
      link(scope, elm, attr);
      scope.isMailingSplit = (mailing, field) => crmMosaicoVariants.isSplit(mailing, field);
    };
    return d;
  });
})(angular, CRM.$, CRM._);
