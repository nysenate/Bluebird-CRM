(function(angular, $, _) {

  angular.module('crmMosaico').controller('CrmMosaicoCreateFromBaseTemplate', function($scope, crmStatus, dialogService, crmMosaicoTemplates) {
    var ts = $scope.ts = CRM.ts('mosaico');
    var ctrl = $scope.$ctrl = this;

    this.model = {};

    crmMosaicoTemplates.whenLoaded().then(function() {
      ctrl.bases = _.sortBy(crmMosaicoTemplates.getBases(), 'type');
      ctrl.model.base = ctrl.bases[0].baseDetails.name;
    });

    this.create = function() {
      crmStatus({},
        crmMosaicoTemplates.create(ctrl.model)
      )
      .then(function(tpl) {
        crmMosaicoTemplates.edit(tpl);
        dialogService.close('crmMosaicoCreatePopup');
      });
    };
  });

})(angular, CRM.$, CRM._);
