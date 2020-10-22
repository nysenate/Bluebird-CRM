(function (angular, _) {
  var module = angular.module('civicase.data');

  module.service('monthNavMockData', [function () {
    return {
      /**
       * Returns a list of mocked month nav data
       *
       * @returns {Array} month mock data
       */
      get: function () {
        var futureMonth = moment().add(1, 'month');
        var currentMonth = moment();
        var pastMonth = moment().add(-1, 'month');
        var monthNavMockData = [{
          count: 9,
          month: futureMonth.format('MM'),
          year: futureMonth.format('YYYY')
        }, {
          count: 10,
          month: currentMonth.format('MM'),
          year: currentMonth.format('YYYY')
        }, {
          count: 11,
          month: pastMonth.format('MM'),
          year: pastMonth.format('YYYY')
        }];

        return _.clone(monthNavMockData);
      }
    };
  }]);
})(angular, CRM._);
