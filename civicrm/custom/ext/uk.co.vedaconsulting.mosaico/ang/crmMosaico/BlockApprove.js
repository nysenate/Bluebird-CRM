(function(angular, $, _) {
  angular.module('crmMosaico').directive('crmMosaicoBlockApprove', function(crmMailingSimpleDirective) {
    return crmMailingSimpleDirective('crmMosaicoBlockApprove', '~/crmMosaico/BlockApprove.html');
  });
})(angular, CRM.$, CRM._);
