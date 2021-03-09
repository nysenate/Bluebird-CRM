/* eslint-env jasmine */

((loadForm) => {
  describe('crm load form service', () => {
    let civicaseCrmLoadForm;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_civicaseCrmLoadForm_) => {
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;
    }));

    describe('when calling service', () => {
      beforeEach(() => {
        civicaseCrmLoadForm('param1', 'param2');
      });

      it('calls the loadform function defined in core with same parameters', () => {
        expect(loadForm).toHaveBeenCalledWith('param1', 'param2');
      });
    });
  });
})(CRM.loadForm);
