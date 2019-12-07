(function(angular, $, _) {
  angular.module('crmMosaico').directive('crmMosaicoBlockMailing', function(crmMailingSimpleDirective) {
    return crmMailingSimpleDirective('crmMosaicoBlockMailing', '~/crmMosaico/BlockMailing.html');
  });
})(angular, CRM.$, CRM._);
