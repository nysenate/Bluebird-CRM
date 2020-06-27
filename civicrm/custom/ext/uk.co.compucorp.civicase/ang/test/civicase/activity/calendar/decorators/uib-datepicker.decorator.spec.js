/* eslint-env jasmine */

(function (angular) {
  describe('day picker decorator', function () {
    var $compile, $rootScope, $scope, daypicker;

    beforeEach(module('civicase', 'ui.bootstrap'));

    beforeEach(inject(function (_$compile_, _$rootScope_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
    }));

    describe('current week day', function () {
      beforeEach(function () {
        jasmine.clock().install();
      });

      afterEach(function () {
        jasmine.clock().uninstall();
      });

      describe('when the week day is Sunday', function () {
        beforeEach(function (done) {
          setupCUrrentWeekDayTest(moment().isoWeekday(0), done);
        });

        it('marks Sunday as the current week day on the calendar', function () {
          expect(daypicker.currentWeekDay).toBe(6);
        });
      });

      describe('when the week day is Monday', function () {
        beforeEach(function (done) {
          setupCUrrentWeekDayTest(moment().isoWeekday(1), done);
        });

        it('marks Sunday as the current week day on the calendar', function () {
          expect(daypicker.currentWeekDay).toBe(0);
        });
      });

      describe('when the week day is Friday', function () {
        beforeEach(function (done) {
          setupCUrrentWeekDayTest(moment().isoWeekday(5), done);
        });

        it('marks Sunday as the current week day on the calendar', function () {
          expect(daypicker.currentWeekDay).toBe(4);
        });
      });

      /**
       * Setups the test for the current week day by mocking the clock's date,
       * initializing the directive and ticking the clock forward.
       *
       * @param {Object} momentDate a reference to a moment date.
       * @param {Function} done the function to execute after the clock moves forward.
       */
      function setupCUrrentWeekDayTest (momentDate, done) {
        jasmine.clock().mockDate(momentDate.toDate());
        initDirective();
        setTimeout(done);
        jasmine.clock().tick();
      }
    });

    /**
     * Initializes the day picker directive. Since it requires the date picker directive
     * the day picker is wrapped around the date picker.
     */
    function initDirective () {
      var element;
      var html = '<div uib-datepicker ng-model="dt"><div uib-daypicker></div></div>';
      $scope = $rootScope.$new();
      element = $compile(html)($scope);

      $rootScope.$digest();

      daypicker = element.find('[uib-daypicker]').scope();
    }
  });
})(angular);
