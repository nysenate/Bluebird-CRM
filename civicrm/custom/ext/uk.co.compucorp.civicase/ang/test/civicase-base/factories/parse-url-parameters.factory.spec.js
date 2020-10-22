/* eslint-env jasmine */
(() => {
  describe('parseUrlParameters', () => {
    let parseUrlParameters;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_parseUrlParameters_) => {
      parseUrlParameters = _parseUrlParameters_;
    }));

    describe('when parsing a URL', () => {
      let parsedUrlResult;
      const testUrl = '/civicrm/a?cid=999&case_id=888&category=custom-category';
      const expectedResult = {
        cid: '999',
        case_id: '888',
        category: 'custom-category'
      };

      beforeEach(() => {
        parsedUrlResult = parseUrlParameters(testUrl);
      });

      it('returns the URL parameters as an object', () => {
        expect(parsedUrlResult).toEqual(expectedResult);
      });
    });
  });
})();
