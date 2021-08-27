(function(angular, $, _) {

  angular.module('contactlayout').component('contactLayoutEditTabs', {
    bindings:  {
      defaults: '=',
      layout: '<',
      contactType: '<',
    },
    templateUrl: '~/contactlayout/contactLayoutEditTabs.html',
    controller: function($scope) {
      var ts = $scope.ts = CRM.ts('contactlayout'),
        ctrl = this,
        editingTabIcon,
        allTabs = _.indexBy(CRM.vars.contactlayout.tabs, 'id');

      // Settings for ui-sortable
      this.sortableOptions = {
        containment: '#cse-tabs-container',
        cancel: 'input,textarea,button,select,option,a,.crm-editable-enabled,[contenteditable]'
      };

      // Toggle between using defaults & custom tabs
      this.toggleTabs = function() {
        if (ctrl.layout.tabs) {
          ctrl.layout.tabs = null;
        } else {
          ctrl.layout.tabs = angular.copy(ctrl.defaults);
        }
      };

      this.toggleTabActive = function(tab) {
        tab.is_active = !tab.is_active;
        if (!tab.is_active) {
          tab.title = allTabs[tab.id].title;
        }
      };

      this.pickTabIcon = function(tab) {
        editingTabIcon = tab;
        $('#cse-icon-picker ~ .crm-icon-picker-button').click();
      };

      CRM.loadScript(CRM.config.resourceBase + 'js/jquery/jquery.crmIconPicker.js').done(function() {
        $('#cse-icon-picker').crmIconPicker().change(function() {
          if (editingTabIcon) {
            $scope.$apply(function() {
              editingTabIcon.icon = 'crm-i ' + $('#cse-icon-picker').val();
              editingTabIcon = null;
              $('#cse-icon-picker').val('').change();
            });
          }
        });
      });
    }
  });

})(angular, CRM.$, CRM._);
