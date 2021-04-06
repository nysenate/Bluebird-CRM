(function (angular, _) {
  const module = angular.module('civicase.data');

  CRM.civicase.caseActions = [{
    title: 'Print Case',
    action: 'Print',
    number: 1,
    icon: 'fa-print',
    is_write_action: false
  }, {
    title: 'Lock Case',
    action: 'LockCases',
    number: 1,
    icon: 'fa-lock',
    is_write_action: true
  }, {
    title: 'Email Case Manager',
    action: 'EmailManagers',
    icon: 'fa-envelope-o',
    is_write_action: true
  }, {
    title: 'Delete Case',
    action: 'DeleteCases',
    icon: 'fa-trash',
    is_write_action: true
  }, {
    title: 'Webforms',
    action: 'Webforms',
    icon: 'fa-file-text-o',
    is_write_action: false,
    items: [{
      title: 'Case Webform',
      action: 'GoToWebform',
      path: 'content/case-webform',
      case_type_ids: ['3', '1'],
      clientID: '1',
      icon: 'fa-link',
      is_write_action: false
    }, {
      title: 'Award Webform',
      action: 'GoToWebform',
      path: 'content/award-webform',
      case_type_ids: ['4'],
      clientID: '1',
      icon: 'fa-link',
      is_write_action: false
    }, {
      title: 'Not Assigned Webform',
      action: 'GoToWebform',
      path: 'content/not-assigned-webform',
      case_type_ids: [],
      clientID: '1',
      icon: 'fa-link',
      is_write_action: false
    }]
  }];

  module.service('CaseActionsData', function () {
    this.get = function () {
      return _.cloneDeep(CRM.civicase.caseActions);
    };
  });
}(angular, CRM._));
