(function (angular) {
  var module = angular.module('civicase');
  var $ = angular.element;

  /**
   * Directive for the sticky table header functionality on case list page
   */
  module.directive('civicaseStickyTableHeader', function ($rootScope, $timeout) {
    return {
      restrict: 'A',
      link: civicaseStickyTableHeaderLink
    };

    /**
     * Link function for stickyTableHeader Directive
     *
     * @param {object} scope
     *   Scope under which directive is called
     * @param {object} $el
     *   Element on which directive is called
     * @param {object} attrs
     *   attributes of directive
     */
    function civicaseStickyTableHeaderLink (scope, $el, attrs) {
      var $toolbarDrawer = $('#toolbar');
      var $table = $el;
      var $header = $el.find('thead');

      (function init () {
        initWatchers();
        initSubscribers();
      }());

      /**
       * Initialise all watchers
       */
      function initWatchers () {
        scope.$watch('isLoading', fixPositioning);
        scope.$watch('caseIsFocused', fixPositioning);
        scope.$watch('viewingCase', fixPositioning);
      }

      /**
       * Initialise all subscribers
       */
      function initSubscribers () {
        scope.$on('civicase::case-search::dropdown-toggle', reAdjustFixedHeader);
        $rootScope.$on('civicase::bulk-actions::bulk-message-toggle', reAdjustFixedHeader);
      }

      /**
       * Loads only if loading completes and case is not focused and
       * not viewing the case details for fixed header
       *
       * @param {boolean} loading
       */
      function fixPositioning () {
        if (!scope.loading && !scope.caseIsFocused && !scope.viewingCase) {
          affixTableHeader();
        }
      }

      /**
       * Checks if the affix header is already affixed,
       * if yes, readjusts it,
       * otherwise inits the affix
       */
      function affixTableHeader () {
        if ($header.data('bs.affix')) {
          reAdjustFixedHeader();
        } else {
          computeFixPositioning();
        }
      }

      /**
       * Assign min-width values to th to have solid grid
       * Timeout if 0s added to execute logic after DOM repainting completes
       */
      function computeFixPositioning () {
        $timeout(function () {
          var bodyPadding = parseInt($('body').css('padding-top'), 10); // to see the space for fixed menus
          var topPos = $header.offset().top - bodyPadding;
          $('th', $header).each(function () {
            $(this).css('min-width', $(this).outerWidth() + 'px');
          });

          // Define when to make the element sticky (affixed)
          $header.affix({
            offset: {
              top: topPos
            }
          })
          // After element is affixed set scrolling pos (to avoid glitch) and top position
            .on('affixed.bs.affix', function () {
              $header.scrollLeft($table.scrollLeft()); // Bind scrolling

              $header.css('top', bodyPadding + 'px'); // Set top pos to body padding so that it don't overlap with the toolbar
              $toolbarDrawer.is(':visible') && $table.css('padding-top', $header.height() + 'px'); // Add top padding to remove the glitch when header moves out of DOM relative position
            })
            .on('affixed-top.bs.affix', function () {
              $header.css('top', 0); // Resets top pos when in default state
              $table.css('padding-top', 0); // Resets padding top when in default state
            });

          // Attach scroll function
          $table.scroll(function () {
            $header.scrollLeft($(this).scrollLeft());
          });
        }, 0);
      }

      /**
       * Subscriber for fixed header
       */
      function reAdjustFixedHeader () {
        $timeout(function () {
          var bodyPadding = parseInt($('body').css('padding-top'), 10); // to see the space for fixed menus

          if ($header.data('bs.affix')) {
            $header.data('bs.affix').options.offset.top = $header.offset().top - bodyPadding;
          }
        });
      }
    }
  });
})(angular);
