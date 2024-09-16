(function(angular, $, _) {

  angular.module('contactlayout').component('contactLayoutEditTabs', {
    bindings:  {
      defaults: '<',
      layout: '<',
      contactType: '<',
      isSystem: '<'
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

      this.$onInit = () => {
        // Check default & active tabsets for any missing tabs (e.g. from a newly-enabled extension)
        this.defaultTabs = angular.copy(this.defaults);
        CRM.vars.contactlayout.tabs.forEach((tab) => {
          if (!_.findWhere(ctrl.defaultTabs, {id: tab.id})) {
            ctrl.defaultTabs.push(angular.copy(tab));
          }
          if (ctrl.layout.tabs && !_.findWhere(ctrl.layout.tabs, {id: tab.id})) {
            ctrl.layout.tabs.push(angular.copy(tab));
          }
        });
      };

      // Toggle between using defaults & custom tabs
      this.toggleTabs = function() {
        if (ctrl.layout.tabs) {
          ctrl.layout.tabs = null;
        } else {
          ctrl.layout.tabs = angular.copy(ctrl.defaultTabs);
        }
      };

      this.toggleTabActive = function(tab) {
        tab.is_active = !tab.is_active;
        if (!tab.is_active) {
          tab.title = allTabs[tab.id].title;
        }
      };

      this.tabIsValid = function(tab) {
        return !tab.contact_type || !ctrl.contactType || tab.contact_type.includes(ctrl.contactType);
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
