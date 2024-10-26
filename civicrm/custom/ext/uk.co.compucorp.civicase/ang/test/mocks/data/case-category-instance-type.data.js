(function (angular, _) {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].caseCategoryInstanceType = {
    1: {
      value: '1',
      label: 'Case Management',
      name: 'case_management',
      grouping: 'CRM_Civicase_Service_CaseManagementUtils',
      is_active: '1',
      weight: '1',
      filter: '0'
    },
    2: {
      value: '2',
      label: 'Applicant Management',
      name: 'applicant_management',
      grouping: 'CRM_CiviAwards_Service_ApplicantManagementUtils',
      is_active: '1',
      weight: '2',
      filter: '0'
    }
  };

  module.service('CaseCategoryInstanceTypeData', function () {
    return {
      /**
       * Returns case category instance types
       *
       * @returns {object} case category instance types data.
       */
      get: function () {
        return _.clone(CRM['civicase-base'].caseCategoryInstanceType);
      }
    };
  });
})(angular, CRM._);
