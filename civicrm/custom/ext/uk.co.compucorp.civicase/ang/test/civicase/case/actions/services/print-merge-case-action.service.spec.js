(function (_, $) {
  describe('MoveCopyActivityAction', function () {
    var $rootScope, PrintMergeCaseAction, CasesMockData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_$rootScope_, _PrintMergeCaseAction_, _CasesData_) {
      $rootScope = _$rootScope_;
      PrintMergeCaseAction = _PrintMergeCaseAction_;
      CasesMockData = _CasesData_;
    }));

    describe('getPath()', function () {
      var caseObj, returnValue;

      beforeEach(function () {
        caseObj = CasesMockData.get().values[0];

        PrintMergeCaseAction.doAction([caseObj])
          .then(function (result) {
            returnValue = result;
          });

        $rootScope.$digest();
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

    describe('visibility of the action', () => {
      var caseObj, isVisible;

      describe('when inside the bulk action', () => {
        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          isVisible = PrintMergeCaseAction.isActionAllowed('email', [caseObj], { mode: 'case-bulk-actions' });
        });

        it('shows the action', () => {
          expect(isVisible).toBe(true);
        });
      });

      describe('when outside the bulk block', () => {
        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          isVisible = PrintMergeCaseAction.isActionAllowed('email', [caseObj], { mode: 'not-case-bulk-actions' });
        });

        it('hides the action', () => {
          expect(isVisible).toBe(false);
        });
      });
    });
  });
})(CRM._, CRM.$);
