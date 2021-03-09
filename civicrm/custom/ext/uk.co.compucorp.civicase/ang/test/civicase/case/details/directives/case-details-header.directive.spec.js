/* eslint-env jasmine */
(function (_) {
  describe('civicaseCaseDetailsHeaderController', function () {
    var $controller, $rootScope, $scope, CasesData,
      CaseActionsData, CaseActions, WebformsCaseAction, webformsList,
      GoToWebformCaseAction;

    beforeEach(module('civicase.templates', 'civicase', 'civicase.data'));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_, _CasesData_,
      _CaseActionsData_, _CaseActions_, _WebformsCaseAction_, _webformsList_,
      _GoToWebformCaseAction_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      CasesData = _CasesData_;
      CaseActionsData = _CaseActionsData_;
      CaseActions = _CaseActions_;
      WebformsCaseAction = _WebformsCaseAction_;
      GoToWebformCaseAction = _GoToWebformCaseAction_;
      webformsList = _webformsList_;
    }));

    describe('webform dropdown visibility', () => {
      beforeEach(() => {
        spyOn(WebformsCaseAction, 'isActionAllowed');
        spyOn(CaseActions, 'findByActionName').and.returnValue(CaseActionsData.get()[0]);

        webformsList.buttonLabel = 'Webforms List';
        initController();
      });

      it('displays a list of webforms when settings is on', () => {
        expect(WebformsCaseAction.isActionAllowed).toHaveBeenCalledWith(
          CaseActionsData.get()[0], [$scope.item], { mode: 'case-details-header' }
        );
      });

      it('sets the label of the dropdown, same as the settings', () => {
        expect($scope.webformsListButtonLabel).toBe('Webforms List');
      });

      describe('when clicking on a different case', () => {
        beforeEach(() => {
          $scope.item = _.cloneDeep(CasesData.get().values[1]);
          $scope.$digest();
        });

        it('updates the webforms list with respect to newly selected case', () => {
          expect(WebformsCaseAction.isActionAllowed).toHaveBeenCalledWith(
            CaseActionsData.get()[0], [_.cloneDeep(CasesData.get().values[1])], { mode: 'case-details-header' }
          );
        });
      });
    });

    describe('visibilty of a specific item in webform dropdown', () => {
      beforeEach(() => {
        initController();
        spyOn(GoToWebformCaseAction, 'isActionAllowed');
        $scope.isGoToWebformAllowed('action');
      });

      it('displays only those webforms which are available for the current case', () => {
        expect(GoToWebformCaseAction.isActionAllowed)
          .toHaveBeenCalledWith('action', [$scope.item]);
      });
    });

    describe('when clicking on a webform dropdown item', () => {
      beforeEach(() => {
        initController();
        spyOn(GoToWebformCaseAction, 'doAction');
        $scope.openWebform('action');
      });

      it('opens the webform in a new tab', () => {
        expect(GoToWebformCaseAction.doAction)
          .toHaveBeenCalledWith([$scope.item], 'action');
      });
    });

    /**
     * Initializes the case details controller.
     *
     * @param {object} caseItem a case item to pass to the controller. Defaults to
     * a case from the mock data.
     */
    function initController (caseItem) {
      $scope = $rootScope.$new();
      $scope.item = caseItem || _.cloneDeep(CasesData.get().values[0]);

      $controller('civicaseCaseDetailsHeaderController', {
        $scope: $scope
      });
    }
  });
})(CRM._);
