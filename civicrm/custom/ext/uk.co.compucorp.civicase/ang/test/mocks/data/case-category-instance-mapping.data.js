(function (angular, CRM, _) {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].caseCategoryInstanceMapping = {
    1: {
      id: '1',
      category_id: '3',
      instance_id: '2'
    },
    2: {
      id: '2',
      category_id: '1',
      instance_id: '1'
    }
  };

  module.service('CaseCategoryInstanceMappingData', function () {
    return {
      /**
       * Returns case category instance mapping data
       *
       * @returns {object} case category instance mapping data.
       */
      get: function () {
        return _.clone(CRM['civicase-base'].caseCategoryInstanceMapping);
      }
    };
  });
})(angular, CRM, CRM._);
