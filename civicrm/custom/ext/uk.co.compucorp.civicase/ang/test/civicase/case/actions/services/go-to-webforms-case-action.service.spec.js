(function (_, $) {
  describe('GoToWebformCaseAction', function () {
    var GoToWebformCaseAction, CaseActionsData, CasesData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_GoToWebformCaseAction_, _CaseActionsData_, _CasesData_) {
      GoToWebformCaseAction = _GoToWebformCaseAction_;
      CaseActionsData = _CaseActionsData_;
      CasesData = _CasesData_.get().values;
    }));

    describe('checkIfWebformVisible()', function () {
      let goToWebformAction;

      describe('when webform has a case type id assigned', function () {
        describe('and viewing a case type which is selected for the webform', () => {
          beforeEach(function () {
            goToWebformAction = _.find(CaseActionsData.get(), function (action) {
              return action.action === 'Webforms';
            }).items[0];
          });

          it('displays the action link', function () {
            expect(GoToWebformCaseAction.checkIfWebformVisible(goToWebformAction, '3')).toBeTrue();
          });
        });

        describe('and viewing a case type which is not selected for the webform', () => {
          beforeEach(function () {
            goToWebformAction = _.find(CaseActionsData.get(), function (action) {
              return action.action === 'Webforms';
            }).items[0];
          });

          it('does not display the action link', function () {
            expect(GoToWebformCaseAction.checkIfWebformVisible(goToWebformAction, '5')).toBeFalse();
          });
        });
      });

      describe('when webform has no case type id assigned', function () {
        beforeEach(function () {
          goToWebformAction = _.find(CaseActionsData.get(), function (action) {
            return action.action === 'Webforms';
          }).items[2];
        });

        it('displays the action link', function () {
          expect(GoToWebformCaseAction.checkIfWebformVisible(goToWebformAction, '2')).toBeTrue();
        });
      });
    });

    describe('when clicking on the webform', function () {
      beforeEach(function () {
        spyOn(CRM, 'alert');

        var cases = [CasesData[0]];
        var goToWebformAction = _.find(CaseActionsData.get(), function (action) {
          return action.action === 'Webforms';
        }).items[0];

        GoToWebformCaseAction.doAction(cases, goToWebformAction);
      });

      it('shows an information popup to refresh the page', function () {
        expect(CRM.alert).toHaveBeenCalledWith(
          ts('Please refresh this page to view updates from the webform submission.'),
          ts('Refresh'),
          'info',
          { expires: 1800000 }
        );
      });
    });
  });
})(CRM._, CRM.$);
