/* eslint-env jasmine */
(($) => {
  describe('civicaseCaseCard', () => {
    let element, $compile, $rootScope, $route, $scope, CasesData;

    beforeEach(module('civicase.templates', 'civicase', 'civicase.data', ($provide) => {
      $route = { current: { params: {} } };

      $provide.value('$route', $route);

      killDirective('civicaseContactCard');
    }));

    beforeEach(inject((_$compile_, _$rootScope_, _CasesData_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      CasesData = _CasesData_.get();
    }));

    beforeEach(() => {
      compileDirective(CasesData.values[0]);
    });

    describe('basic test', () => {
      it('complies the case card directive', () => {
        expect(element.hasClass('civicase__case-card')).toBe(true);
      });
    });

    describe('toggleSelected()', () => {
      let originalEmitFunction;

      beforeEach(() => {
        originalEmitFunction = element.isolateScope().$emit;
        element.isolateScope().$emit = jasmine.createSpy('$emit');
      });

      afterEach(() => {
        element.isolateScope().$emit = originalEmitFunction;
      });

      describe('when selection is true', () => {
        beforeEach(() => {
          element.isolateScope().data.selected = true;
          element.isolateScope().toggleSelected();
        });

        it('sets selection to false', () => {
          expect(element.isolateScope().data.selected).toBe(false);
        });

        it('emits civicase::bulk-actions::check-box-toggled event', () => {
          expect(element.isolateScope().$emit).toHaveBeenCalledWith('civicase::bulk-actions::check-box-toggled', element.isolateScope().data);
        });
      });

      describe('when selection is false', () => {
        beforeEach(() => {
          element.isolateScope().data.selected = false;
          element.isolateScope().toggleSelected();
        });

        it('sets selection to true', () => {
          expect(element.isolateScope().data.selected).toBe(true);
        });

        it('emits civicase::bulk-actions::check-box-toggled event', () => {
          expect(element.isolateScope().$emit).toHaveBeenCalledWith('civicase::bulk-actions::check-box-toggled', element.isolateScope().data);
        });
      });
    });

    /**
     * Function responsible for setting up compilation of the directive
     *
     * @param {object} caseObj card object
     */
    function compileDirective (caseObj) {
      element = $compile('<civicase-case-card case="case"></civicase-case-card>')($scope);
      $scope.case = caseObj;
      $scope.$digest();
    }

    /**
     * Mocks a directive
     * TODO: Have a more generic usage - Maybe create a service/factory
     *
     * @param {string} directiveName name of the directive
     */
    function killDirective (directiveName) {
      angular.mock.module(function ($compileProvider) {
        $compileProvider.directive(directiveName, function () {
          return {
            priority: 9999999,
            terminal: true
          };
        });
      });
    }
  });
})(CRM.$);
