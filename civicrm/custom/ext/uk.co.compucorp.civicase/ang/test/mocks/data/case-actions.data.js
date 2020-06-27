(function (angular, _) {
  const module = angular.module('civicase.data');

  CRM.civicase.caseActions = [{
    title: 'Print Case',
    action: 'Print',
    number: 1,
    icon: 'fa-print'
  }, {
    title: 'Email Case Manager',
    action: 'EmailManagers',
    icon: 'fa-envelope-o'
  }, {
    title: 'Delete Case',
    action: 'DeleteCases',
    icon: 'fa-trash'
  }, {
    title: 'Webforms',
    action: 'Webforms',
    icon: 'fa-file-text-o',
    items: [{
      title: 'Case Webform',
      action: 'GoToWebform',
      path: 'content/case-webform',
      case_type_ids: ['3', '1'],
      clientID: '1',
      icon: 'fa-link'
    }, {
      title: 'Award Webform',
      action: 'GoToWebform',
      path: 'content/award-webform',
      case_type_ids: ['4'],
      clientID: '1',
      icon: 'fa-link'
    }]
  }];

  module.service('CaseActionsData', function () {
    this.get = function () {
      return _.cloneDeep(CRM.civicase.caseActions);
    };
  });
}(angular, CRM._));
