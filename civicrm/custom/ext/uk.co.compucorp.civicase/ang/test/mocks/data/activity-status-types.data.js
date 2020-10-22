(function () {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].activityStatusTypes = {
    cancelled: [3, 5, 6, 8],
    completed: [2],
    incomplete: [1, 4, 7, 9, 10]
  };

  module.constant('ActivityStatusTypesData', {
    values: CRM['civicase-base'].activityStatusTypes
  });
}());
