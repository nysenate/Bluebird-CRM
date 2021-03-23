/* eslint-env jasmine */

(($, _) => {
  describe('linked cases tab', () => {
    let $controller, $scope, LinkCasesCaseAction, civicaseCrmUrl;

    beforeEach(module('civicase'));

    beforeEach(inject((_civicaseCrmUrl_, _$controller_, _$rootScope_, _LinkCasesCaseAction_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
      $controller = _$controller_;
      $scope = _$rootScope_.$new();
      LinkCasesCaseAction = _LinkCasesCaseAction_;
    }));

    beforeEach(() => {
      $scope.refresh = jasmine.createSpy('refresh');
      $scope.item = {
        id: _.uniqueId(),
        client: [
          { contact_id: _.uniqueId() }
        ]
      };

      initController({ $scope });
    });

    describe('when linking the current case to a different one', () => {
      let $mockForm, expectedLinkCaseForm;

      beforeEach((done) => {
        $mockForm = $('<div></div>');

        CRM.loadForm.and.returnValue($mockForm);
        $scope.linkCase();

        LinkCasesCaseAction.doAction([$scope.item])
          .then(function (linkCaseForm) {
            expectedLinkCaseForm = linkCaseForm;
            done();
          });
        $scope.$digest();
      });

      it('opens the link case form', () => {
        expect(civicaseCrmUrl).toHaveBeenCalledWith(expectedLinkCaseForm.path, expectedLinkCaseForm.query);
      });

      describe('after linking the case', () => {
        beforeEach(() => {
          $mockForm.trigger('crmPopupFormSuccess');
          $mockForm.trigger('crmFormSuccess');
        });

        it('refreshes the case', () => {
          expect($scope.refresh).toHaveBeenCalledWith();
        });
      });
    });

    /**
     * Initialises the linked cases tab controller using the given dependencies.
     *
     * @param {object} dependencies a list of dependencies to pass to the controller.
     */
    function initController (dependencies) {
      $controller('civicaseCaseDetailsLinkedCasesTabController', dependencies);
    }
  });
})(CRM.$, CRM._);
