/* eslint-env jasmine */

(function (_, $) {
  describe('EmailManagersCaseAction', function () {
    var EmailManagersCaseAction, CasesMockData, caseObj, actualCRMAlert;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_EmailManagersCaseAction_, _CasesData_) {
      EmailManagersCaseAction = _EmailManagersCaseAction_;
      CasesMockData = _CasesData_;
    }));

    beforeEach(function () {
      caseObj = CasesMockData.get().values[0];
      actualCRMAlert = CRM.alert;
      CRM.alert = jasmine.createSpy('CRMAlert');
    });

    afterEach(function () {
      CRM.alert = actualCRMAlert;
    });

    describe('doAction()', function () {
      describe('when no case manager assigned', function () {
        beforeEach(function () {
          delete caseObj.manager;

          EmailManagersCaseAction.doAction(caseObj, 'email', jasmine.any(Function));
        });

        it('shows an error messages', function () {
          expect(CRM.alert).toHaveBeenCalledWith('Please add a contact as a case manager.', 'No case managers available', 'error');
        });
      });
    });
  });
})(CRM._, CRM.$);
