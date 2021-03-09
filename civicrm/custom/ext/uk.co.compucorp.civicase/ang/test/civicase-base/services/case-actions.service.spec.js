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

    describe('when getting a specific case action by name', () => {
      let expectedResult, returnedCaseActions;

      beforeEach(() => {
        returnedCaseActions = CaseActions.findByActionName('Print');
        expectedResult = {
          title: 'Print Case',
          action: 'Print',
          number: 1,
          icon: 'fa-print',
          is_write_action: false
        };
      });

      it('returns the case actions for the sent action name', () => {
        expect(returnedCaseActions).toEqual(expectedResult);
      });
    });
  });
})();
