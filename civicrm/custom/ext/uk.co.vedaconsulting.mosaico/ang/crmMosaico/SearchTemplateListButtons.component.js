(function(angular, $, _) {
  angular.module('crmMosaico').component('crmMosaicoSearchTemplateListButtons', {
    bindings: {
      tpl: '<',
      refresh: '&'
    },
    templateUrl: '~/crmMosaico/SearchTemplateListButtons.html',
    controller: function($scope, crmApi4, crmStatus, crmMosaicoTemplates) {
      var ts = $scope.ts = CRM.ts('mosaico'),
        ctrl = this;

      this.copyTpl = function(tpl) {
        crmStatus(
          {start: ts('Copying...'), success: ts('Copied')},
          crmMosaicoTemplates.clone({id: tpl.id, title: tpl.title + ' ' + ts('(copy)')})
        )
        .then(function(newTpl) {
          return ctrl.editTpl(newTpl);
        });
      };

      this.deleteTpl = function(tpl) {
        return crmStatus(
          {start: ts('Deleting...'), success: ts('Deleted')},
          crmMosaicoTemplates.delete(tpl)
        ).then(ctrl.refresh);
      };

      this.editTpl = function(tpl) {
        crmMosaicoTemplates.edit(tpl)
          .then(ctrl.refresh);
      };

    }
  });
})(angular, CRM.$, CRM._);
