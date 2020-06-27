/* eslint-env jasmine */

(function ($) {
  describe('PopoverAppendToElement', function () {
    var $compile, $rootScope, $timeout, popover, popoverContainer, originalJQueryOffset;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_$compile_, _$rootScope_, _$timeout_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
      originalJQueryOffset = $.fn.offset;

      spyOn($.fn, 'offset');
    }));

    afterEach(function () {
      $.fn.offset = originalJQueryOffset;

      $('.popover-container').remove();
    });

    describe('Appending the popover to another element', function () {
      var expectedPosition;

      beforeEach(function () {
        expectedPosition = { top: 400, left: 300 };

        initDirective({ appendToElement: '#bootstrap-theme' });
        simulatePopoverOpen();
        $.fn.offset.and.returnValue(expectedPosition);
        $timeout.flush();
      });

      afterEach(function () {
        $('.popover-container').remove();
      });

      it('appends the popover to the given element', function () {
        expect($('#bootstrap-theme').children('.popover').length).toBe(1);
      });

      it('positions the popover in the same position as the original popover', function () {
        expect($.fn.offset).toHaveBeenCalledWith();
        expect($.fn.offset).toHaveBeenCalledWith(expectedPosition);
      });
    });

    function initDirective (options) {
      var scope;
      var html = `<div class="popover-container">
        <div id="bootstrap-theme"></div>
        <span
          uib-popover="Popover content"
          civicase-popover-append-to-element="${options.appendToElement}">
          Open Popover
        </span>
      </div>`;

      scope = $rootScope.$new();
      popoverContainer = $compile(html)(scope);

      popoverContainer.css('position', 'relative');
      popoverContainer.appendTo('body');
      $rootScope.$digest();
    }

    function simulatePopoverOpen () {
      popover = $('<div class="popover"></div>');

      popover.insertAfter('[uib-popover]');
      $rootScope.$broadcast('$includeContentLoaded');
      $timeout(function () {
        popover.css({
          position: 'absolute',
          top: 100,
          left: 100
        });
      }, 0, false);
    }
  });
})(CRM.$);
