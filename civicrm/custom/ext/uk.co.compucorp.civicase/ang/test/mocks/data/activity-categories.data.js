(function () {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].activityCategories = {
    task: {
      label: 'Task',
      icon: 'fa-check-circle',
      name: 'task',
      is_active: '1'
    },
    file: {
      label: 'File',
      icon: 'fa-file',
      name: 'file',
      is_active: '1'
    },
    communication: {
      label: 'Communication',
      icon: 'fa-comment',
      name: 'communication',
      is_active: '1'
    },
    milestone: {
      label: 'Milestone',
      icon: 'fa-flag',
      name: 'milestone',
      is_active: '1'
    },
    alert: {
      label: 'Alert',
      icon: 'fa-exclamation-triangle',
      name: 'alert',
      is_active: '1'
    },
    system: {
      label: 'System',
      icon: 'fa-info-circle',
      name: 'system',
      is_active: '1'
    }
  };

  module.constant('ActivityStatusTypesData', {
    values: CRM['civicase-base'].activityCategories
  });
}());
