(function (angular, _) {
  var module = angular.module('civicase.data');

  module.service('CasesOverviewStatsData', function () {
    var CasesOverviewStatsDataMockData = {
      values: [
        {
          '1': {
            '1': '47',
            '2': '28'
          },
          '2': {
            '1': '43',
            '2': '28',
            '3': '4'
          },
          'all': {
            '1': 90,
            '2': 56,
            '3': 4
          }
        }
      ]
    };

    return {
      /**
       * Returns a list of mocked cases
       *
       * @return {Array} each array contains an object with the activity data.
       */
      get: function () {
        return _.clone(CasesOverviewStatsDataMockData);
      }
    };
  });
}(angular, CRM._));
