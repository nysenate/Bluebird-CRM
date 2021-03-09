/* eslint-env jasmine */

describe('CRM Date Filter', () => {
  let crmDate;

  beforeEach(module('civicase-base', ($provide) => {
    $provide.constant('dateInputFormatValue', 'yy/mm/dd');
  }));

  beforeEach(inject(($filter) => {
    crmDate = $filter('civicaseCrmDate');
  }));

  describe('when passing a string date to the CRM Date filter', () => {
    it('returns the date as expected by date input format value', () => {
      expect(crmDate('1999-12-31T00:00:00')).toBe('1999/12/31');
    });
  });
});
