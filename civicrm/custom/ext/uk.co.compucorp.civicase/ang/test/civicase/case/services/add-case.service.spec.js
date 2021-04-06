(($) => {
  describe('AddCaseService', () => {
    let $window, AddCase, CaseCategoryWebformSettings, civicaseCrmUrl,
      civicaseCrmLoadForm;

    beforeEach(module('civicase-base', 'civicase', ($provide) => {
      $window = { location: { href: '' } };
      CaseCategoryWebformSettings = jasmine.createSpyObj('CaseCategoryWebformSettings', ['getSettingsFor']);

      $provide.value('$window', $window);
      $provide.value('CaseCategoryWebformSettings', CaseCategoryWebformSettings);
    }));

    beforeEach(() => {
      injectDependencies();
      mockFormPopUpDom();
    });

    describe('Button Visibility', () => {
      let isButtonVisible;

      describe('when the user can add new cases', () => {
        beforeEach(() => {
          CRM.checkPerm.and.returnValue(true);

          isButtonVisible = AddCase.isVisible();
        });

        it('displays the button', () => {
          expect(isButtonVisible).toBe(true);
        });
      });

      describe('when the user cannot add new cases', () => {
        beforeEach(() => {
          CRM.checkPerm.and.returnValue(false);

          isButtonVisible = AddCase.isVisible();
        });

        it('does not display the button', () => {
          expect(isButtonVisible).toBe(false);
        });
      });
    });

    describe('click handler', () => {
      describe('when the new case web form url configuration value is defined', () => {
        beforeEach(() => {
          CaseCategoryWebformSettings.getSettingsFor.and.returnValue({
            newCaseWebformUrl: '/someurl',
            newCaseWebformClient: 'cid'
          });

          AddCase.clickHandler({
            caseTypeCategoryName: 'case',
            contactId: '5'
          });
        });

        it('redirects the user to the configured web form url value', () => {
          expect($window.location.href).toBe('/someurl?cid=5');
        });
      });

      describe('when the new case web form url configuration value is not defined', () => {
        beforeEach(() => {
          CaseCategoryWebformSettings.getSettingsFor.and.returnValue({ newCaseWebformUrl: null });
          AddCase.clickHandler({
            caseTypeCategoryName: 'case',
            contactId: '5'
          });
        });

        it('does not redirect the user', () => {
          expect($window.location.href).toBe('');
        });

        it('opens the new case form', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/add', {
            action: 'add',
            case_type_category: 'case',
            civicase_cid: '5',
            context: 'standalone',
            reset: 1
          });
        });
      });
    });

    /**
     * Injects and hoists the dependencies used by this spec file.
     */
    function injectDependencies () {
      inject((_civicaseCrmUrl_, _$window_, _AddCase_, _civicaseCrmLoadForm_) => {
        civicaseCrmUrl = _civicaseCrmUrl_;
        $window = _$window_;
        AddCase = _AddCase_;
        civicaseCrmLoadForm = _civicaseCrmLoadForm_;
      });
    }

    /**
     * Creates a mocked popup element that will be returned by the load form function.
     */
    function mockFormPopUpDom () {
      civicaseCrmLoadForm.and.returnValue($('<div></div>'));
    }
  });
})(CRM.$);
