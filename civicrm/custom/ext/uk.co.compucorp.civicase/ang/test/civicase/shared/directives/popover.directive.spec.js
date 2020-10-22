/* eslint-env jasmine */

(function ($) {
  describe('Popover', function () {
    var $compile, $rootScope, $scope, $sampleReference, $timeout, $toggleButton,
      $uibPosition, popover;

    beforeEach(module('civicase', 'civicase.templates'));

    beforeEach(inject(function (_$compile_, _$rootScope_, _$timeout_, _$uibPosition_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
      $uibPosition = _$uibPosition_;
    }));

    beforeEach(function () {
      $scope = $rootScope.$new();
      $scope.autoCloseOtherPopovers = true;
      $scope.isOpen = false;
      $scope.onOpen = jasmine.createSpy('onOpen');
      $scope.triggerEvent = 'click';

      initDirective();

      // Modifies the position and width for both the sample reference and toggle button elements:
      $()
        .add($sampleReference)
        .add($toggleButton)
        .css({
          position: 'absolute',
          left: '50%',
          width: '10px'
        });
    });

    afterEach(function () {
      removeTestDomElements();
    });

    describe('when the component initializes', function () {
      it('hides the popover content', function () {
        expect(popover.find('civicase-popover-content').is(':visible')).toBe(false);
      });

      it('displays the toggle button', function () {
        expect($toggleButton.is(':visible')).toBe(true);
      });
    });

    describe('opening the popover', function () {
      describe('when clicking on the toggle button', function () {
        var expectedPosition, currentPosition;

        beforeEach(function () {
          $toggleButton.click();
          $rootScope.$digest();

          expectedPosition = getPopoverExpectedPositionUnderElement($toggleButton);
          currentPosition = getPopoverCurrentPosition();
        });

        it('triggers the "on open" event', function () {
          expect($scope.onOpen).toHaveBeenCalled();
        });

        it('displays the popover content', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(true);
        });

        it('displays the popover under the toggle button', function () {
          expect(currentPosition).toEqual(expectedPosition);
        });

        it('appends the popover to the bootstrap theme container', function () {
          expect($('#bootstrap-theme .popover').length).toBe(1);
        });

        it('aligns the popover arrow to the middle of the popover', function () {
          expect(popover.find('.arrow').css('left')).toBe('50%');
        });
      });

      describe('when "is open" is set to true', function () {
        beforeEach(function () {
          $scope.isOpen = true;
          $rootScope.$digest();
        });

        it('displays the popover content', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(true);
        });
      });
    });

    describe('when the popover is open', function () {
      beforeEach(function () {
        popover.find('civicase-popover').isolateScope().isOpen = true;
      });

      describe('and the user clicks outside of the popover', function () {
        beforeEach(function () {
          $('body').click();
        });

        it('closes the popover', function () {
          expect($('civicase-popover-content').is(':visible')).toBe(false);
        });

        it('does not call the "on open" event', function () {
          expect($scope.onOpen).not.toHaveBeenCalled();
        });
      });
    });

    describe('opening the popover on top of a specific element', function () {
      var currentPosition, expectedPosition;

      describe('when the position reference is provided', function () {
        beforeEach(function () {
          $scope.positionReference = $sampleReference;
          $scope.isOpen = true;

          $scope.$digest();

          expectedPosition = getPopoverExpectedPositionUnderElement($sampleReference);
          currentPosition = getPopoverCurrentPosition();
        });

        it('displays the popover under the given element', function () {
          expect(currentPosition).toEqual(expectedPosition);
        });
      });
    });

    describe('when the popover is hidden by the windows border', function () {
      var currentPosition, expectedPosition;

      beforeEach(function () {
        $scope.positionReference = $sampleReference;
        $scope.isOpen = true;
      });

      describe('when the popover is hidden by the windows left border', function () {
        beforeEach(function () {
          $sampleReference.css({ left: 0 });
          $scope.$digest();

          expectedPosition = getPopoverExpectedPositionUnderElement($sampleReference, 'bottom-left');
          currentPosition = getPopoverCurrentPosition();
        });

        it('displays the popover inside of the window', function () {
          expect(currentPosition).toEqual(expectedPosition);
        });

        it('aligns the popover arrow to the left', function () {
          var arrowCurrentPosition = popover.find('.arrow').css('left');
          var arrowExpectedPosition = popover.find('.arrow').outerWidth() + 'px';

          expect(arrowCurrentPosition).toBe(arrowExpectedPosition);
        });
      });

      describe('when the popover is hidden by the windows right border', function () {
        beforeEach(function () {
          $sampleReference.css({ left: $(window).width() });
          $scope.$digest();

          expectedPosition = getPopoverExpectedPositionUnderElement($sampleReference, 'bottom-right');
          currentPosition = getPopoverCurrentPosition();
        });

        it('displays the popover inside of the window', function () {
          expect(currentPosition).toEqual(expectedPosition);
        });

        it('aligns the popover arrow to the right', function () {
          var arrowCurrentPosition, popoverWidth;
          arrowCurrentPosition = popover.find('.arrow').css('left');
          popoverWidth = popover.find('.arrow').outerWidth();

          // calc css property is returned in different format by different
          // version of chromium, hence the following is used
          expect([
            `calc(-${popoverWidth}px + 100%)`,
            `calc(100% + -${popoverWidth}px)`,
            `calc(100% - ${popoverWidth}px)`
          ]).toContain(arrowCurrentPosition);
        });
      });
    });

    describe('hovering over the popover toggle button', function () {
      var HOVER_THRESHOLD = 300;

      beforeEach(function () {
        $scope.triggerEvent = 'hover';
        $scope.positionReference = $sampleReference;

        removeTestDomElements();
        initDirective();
      });

      describe('when hovering over the toggle button', function () {
        beforeEach(function () {
          $toggleButton.trigger('mouseenter');
        });

        it('displays the popover content', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(true);
        });
      });

      describe('when the mouse leaves the toggle button', function () {
        beforeEach(function () {
          $scope.isOpen = true;

          $rootScope.$digest();
          $toggleButton.trigger('mouseleave');
          $timeout.flush(HOVER_THRESHOLD);
        });

        it('hides the popover content', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(false);
        });
      });

      describe('when the popover is open and the mouse moves from the toggle button to the popover', function () {
        beforeEach(function () {
          $scope.isOpen = true;

          $rootScope.$digest();
          $toggleButton.trigger('mouseleave');
          $timeout.flush(HOVER_THRESHOLD - 1);
          popover.find('.popover').trigger('mouseenter');
        });

        it('keeps the popover content visible', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(true);
        });
      });

      describe('when the popover is open and the mouse moves from the popover to the toggle button', function () {
        beforeEach(function () {
          $scope.isOpen = true;

          $rootScope.$digest();
          popover.find('.popover').trigger('mouseleave');
          $timeout.flush(HOVER_THRESHOLD - 1);
          $toggleButton.trigger('mouseenter');
        });

        it('keeps the popover content visible', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(true);
        });
      });

      describe('when the popover is open and the mouse leaves the popover content', function () {
        beforeEach(function () {
          $scope.isOpen = true;

          $rootScope.$digest();
          popover.find('.popover').trigger('mouseleave');
          $timeout.flush(HOVER_THRESHOLD);
        });

        it('hides the popover content', function () {
          expect(popover.find('civicase-popover-content').is(':visible')).toBe(false);
        });
      });
    });

    describe('automatically closing other popovers', function () {
      describe('when set to automatically close other popovers and this popover closes', function () {
        beforeEach(function () {
          $scope.isOpen = false;

          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
          $toggleButton.click();
          $rootScope.$digest();
        });

        it('closes other popovers', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::popover::close-all');
        });
      });

      describe('when other popovers are not set to automatically close and this popover closes', function () {
        beforeEach(function () {
          $scope.isOpen = true;
          $scope.autoCloseOtherPopovers = false;

          spyOn($rootScope, '$broadcast');
          initDirective();
        });

        afterEach(function () {
          removeTestDomElements();
        });

        it('does not close other popovers', function () {
          expect($rootScope.$broadcast).not.toHaveBeenCalledWith('civicase::popover::close-all');
        });
      });

      describe('when the popover close all event is broadcasted', function () {
        beforeEach(function () {
          $rootScope.$broadcast('civicase::popover::close-all');
          $rootScope.$digest();
        });

        it('closes this popover', function () {
          expect($scope.isOpen).toBe(false);
        });
      });
    });

    /**
     * Returns the current position of the popover element.
     *
     * @returns {object} with the top and left properties representing the popover position.
     */
    function getPopoverCurrentPosition () {
      var $popover = popover.find('.popover');

      return $popover.css(['top', 'left']);
    }

    /**
     * Returns the position the popover should have if positioned against the given
     * element.
     *
     * @param {object} $element element
     * @param {string} direction direction of popover
     * @returns {object} with the top and left properties representing the popover position.
     */
    function getPopoverExpectedPositionUnderElement ($element, direction) {
      var $popover = popover.find('.popover');
      var $popoverArrow = popover.find('.arrow');
      var $bootstrapThemeContainer = $('#bootstrap-theme');
      var position = $uibPosition.positionElements($element, $popover, (direction || 'bottom'), true);
      var bootstrapThemeContainerOffset = $bootstrapThemeContainer.offset();
      var arrowPositionModifier = 0;

      if (direction === 'bottom-left') {
        arrowPositionModifier = $popoverArrow.outerWidth() / 2 * -1;
      } else if (direction === 'bottom-right') {
        arrowPositionModifier = $popoverArrow.outerWidth() / 2;
      }

      return {
        top: position.top - bootstrapThemeContainerOffset.top + 'px',
        left: position.left - bootstrapThemeContainerOffset.left + arrowPositionModifier + 'px'
      };
    }

    /**
     * Initializes the directive and appends it to the body.
     */
    function initDirective () {
      var testHtml = $(`
        <div class="civicase-popover-test">
          <style>
            /* Ensures the popover is smaller than the current window's width: */
            .popover {
              position: absolute;
              width: 100px;
            }

            .arrow {
              padding: 0 11px;
            }
          </style>
          <div id="bootstrap-theme"></div>
          <i class="sample-reference">Sample reference element</i>
          <civicase-popover
            position-reference="positionReference"
            is-open="isOpen"
            trigger-event="{{triggerEvent}}"
            auto-close-other-popovers="autoCloseOtherPopovers"
            on-open="onOpen()">
            <civicase-popover-toggle-button>
              When you click here,
            </civicase-popover-toggle-button>
            <civicase-popover-content>
              Then you can see this.
            </civicase-popover-content>
          </civicase-popover>
        </div>
      `);

      testHtml.appendTo('body');

      popover = $compile(testHtml)($scope);

      $rootScope.$digest();

      $toggleButton = popover.find('civicase-popover-toggle-button');
      $sampleReference = $('.sample-reference');
    }

    /**
     * Removes DOM elements added by this spec.
     */
    function removeTestDomElements () {
      $('.civicase-popover-test').remove();
    }
  });
})(CRM.$);
