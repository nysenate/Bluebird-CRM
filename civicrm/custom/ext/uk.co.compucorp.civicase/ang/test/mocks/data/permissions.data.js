(function () {
  var module = angular.module('civicase.data');
  CRM.permissions = {
    'access CiviMail': true,
    'access all cases and activities': true,
    'add cases': true,
    'administer CiviCRM': true,
    'administer CiviCase': true,
    'approve mailings': false,
    'basic case information': false,
    'create mailings': false,
    'delete in CiviMail': true,
    'edit all contacts': true,
    'edit message templates': true,
    'schedule mailings': false,
    'view all contacts': true
  };

  module.constant('CasePemissions', {
    values: CRM.permissions
  });
}());
