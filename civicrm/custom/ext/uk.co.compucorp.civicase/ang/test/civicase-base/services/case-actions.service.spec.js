/* eslint-env jasmine */

(() => {
  describe('Case Actions', () => {
    let CaseActions, CaseActionsData;

    beforeEach(module('civicase.data', 'civicase'));

    beforeEach(inject((_CaseActions_, _CaseActionsData_) => {
      CaseActions = _CaseActions_;
      CaseActionsData = _CaseActionsData_.get();
    }));

    describe('when getting all case actions', () => {
      let returnedCaseActions;

      beforeEach(() => {
        returnedCaseActions = CaseActions.getAll();
      });

      it('returns all the case actions', () => {
        expect(returnedCaseActions).toEqual(CaseActionsData);
      });
    });
  });
})();
