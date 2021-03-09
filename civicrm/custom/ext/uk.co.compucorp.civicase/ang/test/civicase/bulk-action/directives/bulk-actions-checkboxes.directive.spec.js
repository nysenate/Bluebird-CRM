/* eslint-env jasmine */
(function ($) {
  describe('BulkActionsCheckboxes', function () {
    var $compile, $rootScope, $scope, element;

    beforeEach(module('civicase', 'civicase.templates'));

    beforeEach(inject(function (_$compile_, _$rootScope_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
    }));

    beforeEach(function () {
      compileDirective();
    });

    describe('basic tests', function () {
      it('complies the BulkActionsCheckboxes directive', function () {
        expect(element.html()).toContain('civicase__bulkactions-checkbox');
      });
    });

    describe('showCheckboxes', function () {
      describe('when showCheckboxes is true', function () {
        beforeEach(function () {
          $scope.showCheckboxes = true;
          $scope.$digest();
        });

        it('enables the bulk action selection mode', function () {
          expect(element.find('.civicase__bulkactions-checkbox-toggle .civicase__checkbox--checked').hasClass('civicase__checkbox--checked--hide')).toBe(false);
        });
      });
      describe('when showCheckboxes is false', function () {
        beforeEach(function () {
          $scope.showCheckboxes = false;
          $scope.$digest();
        });

        it('disables the bulk action selection mode', function () {
          expect(element.find('.civicase__bulkactions-checkbox-toggle .civicase__checkbox--checked').hasClass('civicase__checkbox--checked--hide')).toBe(true);
        });
      });
    });

    describe('toggleCheckbox()', function () {
      describe('when showCheckboxes is true', function () {
        beforeEach(function () {
          element.isolateScope().showCheckboxes = true;
          element.isolateScope().toggleCheckbox();
        });

        it('makes showCheckboxes false', function () {
          expect(element.isolateScope().showCheckboxes).toBe(false);
        });
      });
      describe('when showCheckboxes is false', function () {
        beforeEach(function () {
          element.isolateScope().showCheckboxes = false;
          element.isolateScope().toggleCheckbox();
        });

        it('makes showCheckboxes true', function () {
          expect(element.isolateScope().showCheckboxes).toBe(true);
        });
      });
    });

    describe('select()', function () {
      var originalEmitFunction;

      beforeEach(function () {
        originalEmitFunction = element.isolateScope().$emit;
        element.isolateScope().$emit = jasmine.createSpy('$emit');
      });

      afterEach(function () {
        element.isolateScope().$emit = originalEmitFunction;
      });

      describe('when called for all selection', function () {
        beforeEach(function () {
          element.isolateScope().select('all');
        });

        it('emits civicase::bulk-actions::bulk-selections event with "all" parameter', function () {
          expect(element.isolateScope().$emit).toHaveBeenCalledWith('civicase::bulk-actions::bulk-selections', 'all');
        });
      });

      describe('called for visible selection', function () {
        beforeEach(function () {
          element.isolateScope().select('visible');
        });

        it('emits civicase::bulk-actions::bulk-selections event with "visible" parameter', function () {
          expect(element.isolateScope().$emit).toHaveBeenCalledWith('civicase::bulk-actions::bulk-selections', 'visible');
        });
      });

      describe('called for deselect all', function () {
        beforeEach(function () {
          element.isolateScope().select('none');
        });

        it('emits civicase::bulk-actions::bulk-selections event with "none" parameter', function () {
          expect(element.isolateScope().$emit).toHaveBeenCalledWith('civicase::bulk-actions::bulk-selections', 'none');
        });
      });
    });

    /**
     * Compiles the directive
     */
    function compileDirective () {
      $scope.selectedCasesLength = 10;
      $scope.showCheckboxes = true;
      element = $compile('<div civicase-bulk-actions-checkboxes show-checkboxes="showCheckboxes" selected-items="selectedCasesLength"></div>')($scope);
      $scope.$digest();
    }
  });
}(CRM.$));
