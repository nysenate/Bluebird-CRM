/* eslint-env jasmine */

(function (_, $) {
  describe('WebformsCaseAction', function () {
    var WebformsCaseAction, attributes, CaseActionsData, CasesData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_WebformsCaseAction_, _CaseActionsData_, _CasesData_) {
      WebformsCaseAction = _WebformsCaseAction_;
      CaseActionsData = _CaseActionsData_;
      CasesData = _CasesData_.get().values;
    }));

    describe('isActionAllowed()', function () {
      let webformAction, cases;

      beforeEach(function () {
        attributes = {};
        cases = [CasesData[0]];
        webformAction = _.find(CaseActionsData.get(), function (action) {
          return action.action === 'Webforms';
        });
      });

      describe('when used inside case details page', function () {
        beforeEach(function () {
          attributes.mode = 'case-details';
        });

        describe('when webforms are present', () => {
          it('displays the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeTrue();
          });
        });

        describe('when webforms are not present', () => {
          beforeEach(function () {
            webformAction.items = [];
          });

          it('displays the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
          });
        });
      });

      describe('when used outside of case details page', function () {
        describe('when used inside bulk action', () => {
          beforeEach(function () {
            attributes.mode = 'case-bulk-actions';
          });

          it('hides the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
          });
        });

        describe('when used in other pages', function () {
          beforeEach(function () {
            attributes.mode = undefined;
          });

          it('hides the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
          });
        });
      });
    });
  });
})(CRM._, CRM.$);
