/* eslint-env jasmine */

(function (_, $) {
  describe('WebformsCaseAction', function () {
    var WebformsCaseAction, attributes, CaseActionsData, CasesData, webformsList;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_WebformsCaseAction_, _CaseActionsData_,
      _CasesData_, _webformsList_) {
      WebformsCaseAction = _WebformsCaseAction_;
      CaseActionsData = _CaseActionsData_;
      CasesData = _CasesData_.get().values;
      webformsList = _webformsList_;
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

      describe('when webforms should not be visible in a separate list', function () {
        beforeEach(function () {
          webformsList.isVisible = false;
        });

        describe('webforms list in case actions dropdown', () => {
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

            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });
        });

        describe('webforms list in case summary header', () => {
          beforeEach(function () {
            attributes.mode = 'case-details-header';
          });

          describe('when webforms are present', () => {
            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });

          describe('when webforms are not present', () => {
            beforeEach(function () {
              webformAction.items = [];
            });

            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });
        });
      });

      describe('when webforms should be visible in a separate list', function () {
        beforeEach(function () {
          webformsList.isVisible = true;
        });

        describe('webforms list in case actions dropdown', () => {
          beforeEach(function () {
            attributes.mode = 'case-details';
          });

          describe('when webforms are present', () => {
            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });

          describe('when webforms are not present', () => {
            beforeEach(function () {
              webformAction.items = [];
            });

            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });
        });

        describe('webforms list in case summary header', () => {
          beforeEach(function () {
            attributes.mode = 'case-details-header';
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

            it('hides the action link', function () {
              expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalse();
            });
          });
        });
      });

      describe('when used outside of case details page', function () {
        describe('when used inside bulk action', () => {
          beforeEach(function () {
            attributes.mode = 'case-bulk-actions';
          });

          it('hides the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalsy();
          });
        });

        describe('when used in other pages', function () {
          beforeEach(function () {
            attributes.mode = undefined;
          });

          it('hides the action link', function () {
            expect(WebformsCaseAction.isActionAllowed(webformAction, cases, attributes)).toBeFalsy();
          });
        });
      });
    });
  });
})(CRM._, CRM.$);
