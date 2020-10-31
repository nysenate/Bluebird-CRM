/* eslint-env jasmine */
/* global SimpleBar */
/* eslint-disable no-global-assign */
(function (_) {
  describe('civicaseCustomScrollbar', function () {
    var element, $compile, $rootScope, $scope, SimpleBarOriginalFunction, originalRootScopeOn;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_$compile_, _$rootScope_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      $scope.options = {
        'autoHide': true
      };
    }));

    describe('initiate SimpleBar', function () {
      beforeEach(function () {
        SimpleBarOriginalFunction = SimpleBar;
        SimpleBar = jasmine.createSpy('SimpleBar');
      });

      afterEach(function () {
        SimpleBar = SimpleBarOriginalFunction;
      });

      describe('called with default options overrided', function () {
        beforeEach(function () {
          $scope.options = {
            'autoHide': true,
            'otherOptions': 'otherValue'
          };
          compileDirective();
        });

        it('should call SimpleBar()', function () {
          expect(SimpleBar).toHaveBeenCalledWith(element[0], {
            'autoHide': true,
            'otherOptions': 'otherValue'
          });
        });
      });

      describe('called with default options not overrided', function () {
        beforeEach(function () {
          $scope.options = {
            'otherOptions': 'otherValue'
          };
          compileDirective();
        });

        it('should call SimpleBar()', function () {
          expect(SimpleBar).toHaveBeenCalledWith(element[0], {
            'autoHide': false,
            'otherOptions': 'otherValue'
          });
        });
      });
    });

    describe('add subscribers', function () {
      beforeEach(function () {
        originalRootScopeOn = $rootScope.$on;
        $rootScope.$on = jasmine.createSpy('rootScopeOn');
      });

      afterEach(function () {
        $rootScope.$on = originalRootScopeOn;
      });

      beforeEach(function () {
        compileDirective();
      });

      it('should call $rootScope.$on()', function () {
        expect($rootScope.$on).toHaveBeenCalledWith('civicase::custom-scrollbar::recalculate', jasmine.any(Function));
      });
    });

    /**
     * Initialise directive
     */
    function compileDirective () {
      element = $compile('<div civicase-custom-scrollbar scrollbar-config="{{options}}"></div')($scope);
      $scope.$digest();
    }
  });
})(CRM._);
