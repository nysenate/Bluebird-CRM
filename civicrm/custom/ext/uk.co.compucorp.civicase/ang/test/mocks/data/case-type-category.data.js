/* eslint no-param-reassign: "error" */

(function () {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].caseTypeCategories = {
    1: { value: '1', label: 'Cases', name: 'Cases', is_active: '1' },
    2: { value: '2', label: 'Prospecting', name: 'Prospecting', is_active: '1' },
    3: { value: '3', label: 'Awards', name: 'awards', is_active: '1' }
  };

  module.constant('caseTypeCategoriesMockData', CRM['civicase-base'].caseTypeCategories);
}(CRM));
