(function (angular, _) {
  var module = angular.module('civicase.data');

  module.service('OptionValuesMockData', [function () {
    var optionValueMockData = [{
      id: '1',
      option_group_id: '1',
      label: 'Phone',
      value: '1',
      name: 'Phone',
      filter: '0',
      weight: '1',
      is_optgroup: '0',
      is_reserved: '0',
      is_active: '1'
    }, {
      id: '2',
      option_group_id: '1',
      label: 'Email',
      value: '2',
      name: 'Email',
      filter: '0',
      weight: '2',
      is_optgroup: '0',
      is_reserved: '0',
      is_active: '1'
    }];

    return {
      /**
       * Returns a list of mocked cases
       *
       * @returns {Array} each array contains an object with the activity data.
       */
      get: function () {
        return angular.copy(optionValueMockData);
      }
    };
  }]);
})(angular, CRM._);
