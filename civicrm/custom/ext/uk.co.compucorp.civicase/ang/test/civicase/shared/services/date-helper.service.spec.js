/* eslint-env jasmine */
(function ($) {
  describe('DateHelper', function () {
    var DateHelper;

    beforeEach(module('civicase'));

    describe('DateHelper', function () {
      beforeEach(inject(function (_DateHelper_) {
        DateHelper = _DateHelper_;
      }));

      describe('formatDate()', function () {
        it('returns the date in the DD/MM/YYYY format', function () {
          expect(DateHelper.formatDate('2017-11-20 00:00:00', 'DD/MM/YYYY')).toBe('20/11/2017');
        });
      });
    });
  });
}(CRM.$));
