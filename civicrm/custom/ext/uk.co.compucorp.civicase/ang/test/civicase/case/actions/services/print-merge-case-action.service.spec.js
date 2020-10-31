/* eslint-env jasmine */

(function (_, $) {
  describe('MoveCopyActivityAction', function () {
    var PrintMergeCaseAction, CasesMockData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_PrintMergeCaseAction_, _CasesData_) {
      PrintMergeCaseAction = _PrintMergeCaseAction_;
      CasesMockData = _CasesData_;
    }));

    describe('getPath()', function () {
      var caseObj, returnValue;

      beforeEach(function () {
        caseObj = CasesMockData.get().values[0];

        returnValue = PrintMergeCaseAction.doAction([caseObj]);
      });

      it('returns path for opening popup to print/merge document', function () {
        expect(returnValue).toEqual({
          path: 'civicrm/activity/pdf/add',
          query: {
            action: 'add',
            reset: 1,
            context: 'standalone',
            caseid: caseObj.id,
            cid: caseObj.client[0].contact_id
          }
        });
      });
    });
  });
})(CRM._, CRM.$);
