(function (angular, moment) {
  var module = angular.module('civicase.data');

  module.service('datesMockData', function () {
    return {
      yesterday: moment().subtract(1, 'days').format(),
      today: moment().format(),
      tomorrow: moment().add(1, 'days').format(),
      theDayAfterTomorrow: moment().add(2, 'days').format(),
      nextWeek: moment().add(1, 'weeks').format()
    };
  });
})(angular, moment);
