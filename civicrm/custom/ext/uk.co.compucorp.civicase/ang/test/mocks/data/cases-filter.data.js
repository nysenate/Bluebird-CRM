(function () {
  var module = angular.module('civicase.data');
  var filter = {
    case_type_id: [
      'housing_support'
    ]
  };
  var hiddenFilters = {
    hiddenfilter1: {},
    hiddenfilter2: {}
  };

  module.constant('CaseFilters', {
    filter: filter,
    hiddenFilters: hiddenFilters
  });
}());
