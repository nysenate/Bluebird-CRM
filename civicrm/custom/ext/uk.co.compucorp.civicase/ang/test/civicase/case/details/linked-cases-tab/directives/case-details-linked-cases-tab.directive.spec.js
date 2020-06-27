/* eslint-env jasmine */

(($, _, getCrmUrl) => {
  describe('linked cases tab', () => {
    let $controller, $scope, LinkCasesCaseAction;
    const mockLinkedCaseFormurl = {
      path: '/linked-case-form',
      query: 'linked-case-query'
    };

    beforeEach(module('civicase'));

    beforeEach(inject((_$controller_, _$rootScope_) => {
      $controller = _$controller_;
      $scope = _$rootScope_.$new();
    }));

    beforeEach(() => {
      LinkCasesCaseAction = jasmine.createSpyObj('LinkCasesCaseAction', ['doAction']);
      $scope.refresh = jasmine.createSpy('refresh');
      $scope.item = { id: _.uniqueId() };

      LinkCasesCaseAction.doAction.and.returnValue(mockLinkedCaseFormurl);
      initController({
        $scope,
        LinkCasesCaseAction
      });
    });

    describe('when linking the current case to a different one', () => {
      let $mockForm, expecteFormUrl;

      beforeEach(() => {
        $mockForm = $('<div></div>');
        expecteFormUrl = getCrmUrl(mockLinkedCaseFormurl.path, mockLinkedCaseFormurl.query);

        CRM.loadForm.and.returnValue($mockForm);
        $scope.linkCase();
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
