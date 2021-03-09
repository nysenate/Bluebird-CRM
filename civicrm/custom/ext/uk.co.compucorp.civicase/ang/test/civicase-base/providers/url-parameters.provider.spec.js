/* eslint-env jasmine */
(() => {
  describe('UrlParameters', () => {
    let UrlParameters;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_UrlParameters_) => {
      UrlParameters = _UrlParameters_;
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
        parsedUrlResult = UrlParameters.parse(testUrl);
      });

      it('returns the URL parameters as an object', () => {
        expect(parsedUrlResult).toEqual(expectedResult);
      });
    });
  });
})();
