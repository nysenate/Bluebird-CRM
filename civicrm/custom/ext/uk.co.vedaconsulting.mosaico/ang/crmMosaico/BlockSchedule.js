(function(angular, $, _) {
  angular.module('crmMosaico').directive('crmMosaicoBlockSchedule', function(crmMailingSimpleDirective) {
    return crmMailingSimpleDirective('crmMosaicoBlockSchedule', '~/crmMosaico/BlockSchedule.html');
  });
})(angular, CRM.$, CRM._);
