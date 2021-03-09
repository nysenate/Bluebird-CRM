/* eslint-env jasmine */

(($, _, getCrmUrl) => {
  describe('linked cases tab', () => {
    let $controller, $scope, LinkCasesCaseAction;

    beforeEach(module('civicase'));

    beforeEach(inject((_$controller_, _$rootScope_, _LinkCasesCaseAction_) => {
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
      let $mockForm, expecteFormUrl;

      beforeEach((done) => {
        $mockForm = $('<div></div>');

        CRM.loadForm.and.returnValue($mockForm);
        $scope.linkCase();

        LinkCasesCaseAction.doAction([$scope.item])
          .then(function (linkCaseForm) {
            expecteFormUrl = getCrmUrl(linkCaseForm.path, linkCaseForm.query);
            done();
          });
        $scope.$digest();
      });

      it('opens the link case form', () => {
        expect(CRM.loadForm).toHaveBeenCalledWith(expecteFormUrl);
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
})(CRM.$, CRM._, CRM.url);
