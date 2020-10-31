(function ($, _, angular) {
  var module = angular.module('civicase');

  module.directive('civicasePopover', function ($document, $rootScope, $timeout, $uibPosition) {
    return {
      scope: {
        appendTo: '=',
        autoCloseOtherPopovers: '<?',
        isOpen: '=?',
        triggerOnOpenEvent: '&onOpen',
        popoverClass: '@',
        positionReference: '=',
        triggerEvent: '@'
      },
      transclude: {
        toggleButton: '?civicasePopoverToggleButton',
        content: 'civicasePopoverContent'
      },
      templateUrl: '~/civicase/shared/directives/popover.directive.html',
      link: civicasePopoverLink
    };

    function civicasePopoverLink ($scope, $element, attrs, ctrl, $transcludeFn) {
      var $bootstrapThemeContainer, $popover, $popoverArrow, $toggleButton, mouseLeaveTimeout;
      var HOVER_THRESHOLD = 300;
      var ARROW_POSITION_VALUES = {
        'bottom': '50%',
        'bottom-left': '%width%px',
        'bottom-right': 'calc(100% - %width%px)'
      };

      (function init () {
        $bootstrapThemeContainer = $('#bootstrap-theme');
        $toggleButton = $element.find('civicase-popover-toggle-button');
        $scope.isOpen = false;
        $scope.autoCloseOtherPopovers = $scope.autoCloseOtherPopovers !== false;
        $scope.triggerEvent = _.isEmpty($scope.triggerEvent)
          ? 'click'
          : $scope.triggerEvent;

        transcludeElements();
        initWatchers();
        attachEventListeners();
      })();

      /**
       * Switch between open/ closed state
       */
      $scope.togglePopoverState = function () {
        $scope.isOpen = !$scope.isOpen;
      };

      /**
       * Adds the following event listeners to the popover:
       * - The toggle button gets a click listener by default.
       * - When the toggle action is set to hover, the mouseneter and mouseleave events
       *   are attached to the toggle element.
       * - A click event is attached to the body to determine if the user clicked outside
       *   the popover and close it.
       * - The scope listens for `civicase::popover::close-all` events in order to programmatically
       *   close the popover.
       */
      function attachEventListeners () {
        var $body = $('body');
        var closeEventHasBeenAttached = $body.hasClass('civicase__popup-attached');
        var triggerEvent = $scope.triggerEvent === 'hover'
          ? 'mouseenter'
          : $scope.triggerEvent;

        $toggleButton.on(triggerEvent, function (event) {
          if (mouseLeaveTimeout) {
            cancelMouseLeaveTimeout();

            return;
          }

          if (!$scope.isOpen && $scope.autoCloseOtherPopovers) {
            $rootScope.$broadcast('civicase::popover::close-all');
          }

          $scope.togglePopoverState();
          $scope.isOpen && $scope.triggerOnOpenEvent();
          event.stopPropagation();
          $scope.$apply();
        });

        $scope.$on('civicase::popover::close-all', function () {
          $scope.isOpen = false;
        });

        if ($scope.triggerEvent === 'hover') {
          $toggleButton.on('mouseleave', closePopoverAfterDelay);
        }

        if (!closeEventHasBeenAttached) {
          $document.on('click', function ($event) {
            var isNotInsideAPopoverBox = $('.civicase__popover-box').find($event.target).length === 0;

            if (isNotInsideAPopoverBox && $scope.autoCloseOtherPopovers) {
              $rootScope.$broadcast('civicase::popover::close-all');
              $rootScope.$digest();
            }
          });
          $body.addClass('civicase__popup-attached');
        }
      }

      /**
       * Cancels the mouse leave timeout and removes any reference to it.
       */
      function cancelMouseLeaveTimeout () {
        $timeout.cancel(mouseLeaveTimeout);
        mouseLeaveTimeout = null;
      }

      /**
       * Closes the popover after a 300ms delay. Useful when moving the mouse from
       * the toggle button to the popover and vice versa.
       */
      function closePopoverAfterDelay () {
        mouseLeaveTimeout = $timeout(function () {
          $scope.isOpen = false;
          mouseLeaveTimeout = null;
        }, HOVER_THRESHOLD);
      }

      /**
       * Returns the number of pixes the popover needs to be adjusted to take into
       * consideration the position of the popover arrow.
       *
       * @param {String} direction the direction the popover will be aligned to.
       */
      function getArrowPositionModifier (direction) {
        if (direction === 'bottom-left') {
          return $popoverArrow.outerWidth() / 2 * -1;
        } else if (direction === 'bottom-right') {
          return $popoverArrow.outerWidth() / 2;
        } else {
          return 0;
        }
      }

      /**
       * Determines which direction the popover should be displayed as given a position.
       * If the position would make the popover hidden from the viewport, it will return
       * the proper alignment, otherwise it returns "default".
       *
       * @param {Object} position
       * @return {String}
       */
      function getPopoverDirection (position) {
        var directions = {
          'bottom-left': position.left < 0,
          'bottom-right': position.left + $popover.width() > $(window).width()
        };

        return _.findKey(directions, function (isDirectionHidden) {
          return isDirectionHidden;
        }) || 'bottom';
      }

      /**
       * Get the left and top position for the popover relative to the given element
       * and direction.
       *
       * @param {Object} $element the DOM element to use as reference.
       * @param {String} direction which can be "bottom", "bottom-left", "bottom-right", etc.
       *   defaults to "bottom".
       */
      function getPopoverPositionUnderElement ($element, direction) {
        var arrowPositionModifier, position, bootstrapThemeContainerOffset;
        direction = direction || 'bottom';
        position = $uibPosition.positionElements($element, $popover, direction, true);
        bootstrapThemeContainerOffset = $bootstrapThemeContainer.offset();
        arrowPositionModifier = getArrowPositionModifier(direction);

        return {
          top: position.top - bootstrapThemeContainerOffset.top,
          left: position.left - bootstrapThemeContainerOffset.left + arrowPositionModifier
        };
      }

      /**
       * Initiate popover reference
       */
      function initPopoverReference () {
        $popover = $element.find('.popover');
        $popoverArrow = $popover.find('.arrow');

        $popover.appendTo($scope.appendTo ? $($scope.appendTo) : $bootstrapThemeContainer);

        if ($scope.triggerEvent === 'hover') {
          $popover.on('mouseenter', cancelMouseLeaveTimeout);
          $popover.on('mouseleave', closePopoverAfterDelay);
        }
      }

      /**
       * Initiate Watchers
       */
      function initWatchers () {
        $scope.$watchGroup(['isOpen', 'positionReference'], repositionPopover);
      }

      /**
       * Reposition the popover element
       */
      function repositionPopover () {
        var arrowPosition, popoverDirection, position, positionReference;

        if (!$scope.isOpen) {
          return;
        }

        initPopoverReference();

        /**
         * @note The post digest helps determine the real width of the popover because
         * it waits for content to be rendered. $timeout is too slow for this and has a
         * noticeable delay that makes the popover jump for a brief second.
         */
        $scope.$$postDigest(function () {
          positionReference = $scope.positionReference || $toggleButton;
          position = getPopoverPositionUnderElement(positionReference);
          popoverDirection = getPopoverDirection(position);
          arrowPosition = ARROW_POSITION_VALUES[popoverDirection]
            .replace('%width%', $popoverArrow.outerWidth());

          if (popoverDirection !== 'bottom') {
            position = getPopoverPositionUnderElement(positionReference, popoverDirection);
          }

          $popoverArrow.css('left', arrowPosition);
          $popover.css(position);
        });
      }

      /**
       * Transclude elements copy
       */
      function transcludeElements () {
        $transcludeFn($scope, function (clone, scope) {
          $element.find('[ng-transclude="content"]').html(clone);
        }, false, 'content');
      }
    }
  });
})(CRM.$, CRM._, angular);
