/* eslint-env jasmine */

describe('civicaseStickyTableHeader directive', function () {
  var element, $compile, $rootScope, scope, affixReturnValue, affixOriginalFunction;

  beforeEach(module('civicase'));

  beforeEach(inject(function (_$compile_, _$rootScope_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    scope = $rootScope.$new();
  }));

  beforeEach(function () {
    CRM.$('body').append('<div id="toolbar" style="height: 60px"></div>');

    element = $compile(angular.element(`
      <div civicase-sticky-table-header>
        <table>
          <thead>
            <th style="width: 50px">Sample title</th>
            <th style="width: 50px">Sample title</th>
          </thead>
        </table>
      </div>`))(scope);
  });

  beforeEach(function () {
    affixOriginalFunction = CRM.$.fn.affix;
    CRM.$.fn.affix = jasmine.createSpy('affix');
    affixReturnValue = jasmine.createSpyObj('affix', ['on']);
    affixReturnValue.on.and.returnValue(affixReturnValue);
    CRM.$.fn.affix.and.returnValue(affixReturnValue);
  });

  afterEach(function () {
    CRM.$.fn.affix = affixOriginalFunction;

    CRM.$('#toolbar').remove();
  });

  describe('if loading is not complete and case is focused', function () {
    beforeEach(function () {
      scope.isLoading = true;
      scope.caseIsFocused = true;
      scope.$digest();
    });

    it('does not set min-width of the table headers', function () {
      expect(element.find('thead th').css('min-width')).toBe('');
    });

    it('does not makes the header sticky', function () {
      expect(CRM.$.fn.affix).not.toHaveBeenCalledWith(jasmine.objectContaining({offset: {top: jasmine.any(Number)}}));
    });

    it('does not binds the scroll position of table content to the table header', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed.bs.affix', jasmine.any(Function));
    });

    it('does not resets the padding for top header and when the header gets back to its state', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed-top.bs.affix', jasmine.any(Function));
    });
  });

  describe('if loading is not complete and case is not focused', function () {
    beforeEach(function () {
      scope.isLoading = true;
      scope.caseIsFocused = false;
      scope.$digest();
    });

    it('does not set min-width of the table headers', function () {
      expect(element.find('thead th').css('min-width')).toBe('');
    });

    it('does not makes the header sticky', function () {
      expect(CRM.$.fn.affix).not.toHaveBeenCalledWith(jasmine.objectContaining({offset: {top: jasmine.any(Number)}}));
    });

    it('does not binds the scroll position of table content to the table header', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed.bs.affix', jasmine.any(Function));
    });

    it('does not resets the padding for top header and when the header gets back to its state', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed-top.bs.affix', jasmine.any(Function));
    });
  });

  describe('if loading is complete and case is focused', function () {
    beforeEach(inject(function ($timeout) {
      scope.isLoading = false;
      scope.caseIsFocused = true;
      scope.$digest();
    }));

    it('does not set min-width of the table headers', function () {
      expect(element.find('thead th').css('min-width')).toBe('');
    });

    it('does not makes the header sticky', function () {
      expect(CRM.$.fn.affix).not.toHaveBeenCalledWith(jasmine.objectContaining({offset: {top: jasmine.any(Number)}}));
    });

    it('does not binds the scroll position of table content to the table header', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed.bs.affix', jasmine.any(Function));
    });

    it('does not resets the padding for top header and when the header gets back to its state', function () {
      expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed-top.bs.affix', jasmine.any(Function));
    });
  });

  describe('if loading is complete and case is not focussed', function () {
    describe('and not viewing the case', function () {
      beforeEach(inject(function ($timeout) {
        scope.isLoading = false;
        scope.caseIsFocused = false;
        scope.viewingCase = false;
        scope.$digest();
        $timeout.flush(); // Flushing any timeouts used.
      }));

      it('sets min-width of the table headers, same as the width of themselves', function () {
        expect(element.find('thead th').css('min-width'))
          .toBe(element.find('thead th').outerWidth() + 'px');
      });

      it('makes the header sticky', function () {
        expect(CRM.$.fn.affix).toHaveBeenCalledWith(jasmine.objectContaining({offset: {top: jasmine.any(Number)}}));
      });

      it('binds the scroll position of table content to the table header', function () {
        expect(affixReturnValue.on).toHaveBeenCalledWith('affixed.bs.affix', jasmine.any(Function));
      });

      it('resets the padding for top header and when the header gets back to its state', function () {
        expect(affixReturnValue.on).toHaveBeenCalledWith('affixed-top.bs.affix', jasmine.any(Function));
      });
    });

    describe('and viewing the case', function () {
      beforeEach(inject(function ($timeout) {
        scope.isLoading = false;
        scope.caseIsFocused = false;
        scope.viewingCase = true;
        scope.$digest();
      }));

      it('does not set min-width of the table headers', function () {
        expect(element.find('thead th').css('min-width')).toBe('');
      });

      it('does not makes the header sticky', function () {
        expect(CRM.$.fn.affix).not.toHaveBeenCalledWith(jasmine.objectContaining({offset: {top: jasmine.any(Number)}}));
      });

      it('does not binds the scroll position of table content to the table header', function () {
        expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed.bs.affix', jasmine.any(Function));
      });

      it('does not resets the padding for top header and when the header gets back to its state', function () {
        expect(affixReturnValue.on).not.toHaveBeenCalledWith('affixed-top.bs.affix', jasmine.any(Function));
      });
    });
  });

  describe('table list padding', function () {
    var affixEventHandler;

    beforeEach(inject(function ($timeout) {
      element.find('thead').css('height', '100px');
      scope.$digest();
      $timeout.flush();

      affixEventHandler = affixReturnValue.on.calls.argsFor(0)[1];
    }));

    describe('when scrolling and the toolbar drawer is visible', function () {
      beforeEach(inject(function () {
        affixEventHandler();
      }));

      it('adds a padding to the table equal to the table header', function () {
        expect(element.css('padding-top')).toBe(element.find('thead').css('height'));
      });
    });

    describe('when scrolling and the toolbar drawer is not visible', function () {
      beforeEach(function () {
        CRM.$('#toolbar').hide();
        affixEventHandler();
      });

      it('does not add a padding to the table', function () {
        expect(element.css('padding-top')).toBe('');
      });
    });
  });
});
