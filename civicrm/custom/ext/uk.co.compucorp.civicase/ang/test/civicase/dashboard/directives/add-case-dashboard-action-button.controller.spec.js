(($) => {
  describe('AddCaseDashboardActionButtonController', () => {
    let $rootScope, $scope, $controller, $window, AddCase, currentCaseCategory;

    beforeEach(module('civicase-base', 'civicase', ($provide) => {
      $window = { location: { href: '' } };

      $provide.value('$window', $window);
    }));

    beforeEach(() => {
      injectDependencies();
      spyOn(AddCase, 'isVisible');
      spyOn(AddCase, 'clickHandler');
      initController();
    });

    describe('Button Visibility', () => {
      beforeEach(() => {
        $scope.isVisible();
      });

      it('displays the Add Case button only when adequate permission is available', () => {
        expect(AddCase.isVisible).toHaveBeenCalled();
      });
    });

    describe('Click Event', () => {
      beforeEach(() => {
        $scope.clickHandler();
      });

      it('creates a new case', () => {
        expect(AddCase.clickHandler).toHaveBeenCalledWith(jasmine.objectContaining({
          caseTypeCategoryName: currentCaseCategory
        }));
      });
    });

    describe('Redirecting to the user context', () => {
      let addCaseCallback;
      const mockEvent = $.Event();

      beforeEach(() => {
        AddCase.clickHandler.and.callFake((addCaseParams) => {
          addCaseCallback = addCaseParams.callbackFn;
        });
        $scope.clickHandler();
      });

      describe('when the case response contains a user context URL and the user clicked on "Save"', () => {
        let expectedUrl;

        beforeEach(() => {
          addCaseCallback(mockEvent, {
            buttonName: 'upload',
            userContext: '/expected-url'
          });

          expectedUrl = '/expected-url';
        });

        it('redirects the user context URL provided by the response', () => {
          expect($window.location.href).toBe(expectedUrl);
        });
      });

      describe('when the case response does not contain a user context URL', () => {
        beforeEach(() => {
          addCaseCallback(mockEvent, {});
        });

        it('does not redirect the user', () => {
          expect($window.location.href).toBe('');
        });
      });

      describe('when the case response contains a user context URL, but the "Save and New" was clicked', () => {
        beforeEach(() => {
          addCaseCallback(mockEvent, {
            buttonName: 'upload_new',
            userContext: '/some-url'
          });
        });

        it('does not redirect the user', () => {
          expect($window.location.href).toBe('');
        });
      });
    });

    /**
     * Initializes the contact case tab case details controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('AddCaseDashboardActionButtonController', { $scope: $scope });
    }

    /**
     * Injects and hoists the dependencies used by this spec file.
     */
    function injectDependencies () {
      inject((_$location_, _$rootScope_, _$controller_, _AddCase_,
        _currentCaseCategory_) => {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        AddCase = _AddCase_;
        currentCaseCategory = _currentCaseCategory_;
      });
    }
  });
})(CRM.$);
