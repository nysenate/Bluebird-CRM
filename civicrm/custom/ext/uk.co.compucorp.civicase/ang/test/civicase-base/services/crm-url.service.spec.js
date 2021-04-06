((url) => {
  describe('crm url service', () => {
    let civicaseCrmUrl;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_civicaseCrmUrl_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
    }));

    describe('when calling service', () => {
      beforeEach(() => {
        civicaseCrmUrl('param1', 'param2');
      });

      it('calls the url function defined in core with same parameters', () => {
        expect(url).toHaveBeenCalledWith('param1', 'param2');
      });
    });
  });
})(CRM.url);
