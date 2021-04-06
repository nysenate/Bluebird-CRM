describe('civicaseStickyFooterPager directive', function () {
  var element, $compile, $rootScope, scope, offsetOriginalFunction, scrollTopOriginalFunction, $timeout;

  beforeEach(module('civicase'));

  beforeEach(inject(function (_$compile_, _$rootScope_, _$timeout_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    scope = $rootScope.$new();
    $timeout = _$timeout_;
  }));

  beforeEach(function () {
    offsetOriginalFunction = CRM.$.fn.offset;
    scrollTopOriginalFunction = CRM.$.fn.scrollTop;
  });

  beforeEach(function () {
    // Creating a custom function to mock offset() jQuery function
    CRM.$.fn.offset = function () {
      return { top: 1000 };
    };
    element = $compile(angular.element('<div class="parent"><div class="content"></div><div class="civicase__pager" civicase-sticky-footer-pager>Pager</div></div>'))(scope);
    // Setting up the height of the page be adding height to the content
    CRM.$(element).find('.content').height('1000px');
  });

  afterEach(function () {
    CRM.$.fn.offset = offsetOriginalFunction;
    CRM.$.fn.scrollTop = scrollTopOriginalFunction;
  });

  describe('if loading is not complete', function () {
    describe('basic tests', function () {
      beforeEach(function () {
        setupCommonSteps(true, scope, 0);
      });

      it('removes the sticky footer feature', function () {
        expect(element.hasClass('civicase__pager--fixed')).toBe(false);
      });
    });

    describe('when pager is not in view', function () {
      beforeEach(function () {
        setupCommonSteps(true, scope, 0);
      });

      it('should not fix the pager to the footer', function () {
        expect(element.find('.civicase__pager').hasClass('civicase__pager--fixed')).toBe(false);
      });
    });

    describe('when pager is in view', function () {
      beforeEach(function () {
        setupCommonSteps(true, scope, 1200);
      });

      it('should not fix the pager to the footer', function () {
        expect(element.find('.civicase__pager').hasClass('civicase__pager--fixed')).toBe(false);
      });
    });
  });

  describe('if loading is complete', function () {
    describe('when pager is not in view', function () {
      beforeEach(function () {
        setupCommonSteps(false, scope, 0);
      });

      it('should fix the pager to the footer', function () {
        expect(element.find('.civicase__pager').hasClass('civicase__pager--fixed')).toBe(true);
      });
    });

    describe('when pager is in view', function () {
      beforeEach(function () {
        setupCommonSteps(false, scope, 1200);
      });

      it('should not fix the pager to the footer', function () {
        expect(element.find('.civicase__pager').hasClass('civicase__pager--fixed')).toBe(false);
      });
    });
  });

  /**
   * Common setup for tests
   *
   * @param {boolean} loading wheather loading is complete
   * @param {object} scope scope object
   * @param {number} top px from top the screen should scroll to.
   */
  function setupCommonSteps (loading, scope, top) {
    scope.isLoading = loading;
    // Creating a custom function to mock offset() jQuery function
    CRM.$.fn.scrollTop = function () {
      return top;
    };

    scope.$digest();

    if (!loading) {
      $timeout.flush(); // Flushing any timeouts used.
    }
  }
});
