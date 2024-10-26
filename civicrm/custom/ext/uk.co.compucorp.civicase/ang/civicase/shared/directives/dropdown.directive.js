(function ($, angular) {
  var module = angular.module('civicase');

  /**
   * This dropdown menu directive allows for nested dropdowns to work properly
   * and also allows interacting with elements inside of the dropdown without closing
   * it.
   */
  module.directive('civicaseDropdown', function () {
    return {
      link: civicaseDropdownLink
    };

    /**
     * Dropdown Directive's link function definition.
     *
     * @param {object} scope the scope the element is linked to.
     * @param {object} $element a reference to the dropdown element.
     * @param {object} attrs attributes and values attached to the dropdown element.
     */
    function civicaseDropdownLink (scope, $element, attrs) {
      var $toggleElement = $element.find('[civicase-dropdown-toggle]:first');
      var isOpen = false;

      (function () {
        initEventListeners();
        removeEventListenersOnScopeDestroy();
      })();

      /**
       * Closes the dropdown menu when clicking outside of it or when clicking
       *
       * @param {object} event the DOM event that was triggered by the click.
       */
      function closeDropdownOnClickOutside (event) {
        var $dropdownMenu = getChildDropdownMenu();
        var targetIsButtonOrLink = $(event.target).is('button, a');
        var targetIsInsideDropdown = $dropdownMenu[0].contains(event.target);
        var targetIsInsideContainer = $element[0].contains(event.target);
        var targetIsADropdownToggleButton = $(event.target).is('[civicase-dropdown-toggle]');

        if (!targetIsInsideContainer || (targetIsInsideDropdown && targetIsButtonOrLink && !targetIsADropdownToggleButton)) {
          isOpen = false;
          $dropdownMenu.hide();
        }
      }

      /**
       * Closes the dropdown menu when the Escape key is pressed
       *
       * @param {object} event the DOM event that was triggered by the key press.
       */
      function closeDropdownOnEscapeKeyPressed (event) {
        var $dropdownMenu = getChildDropdownMenu();

        if (isOpen && event.key === 'Escape') {
          isOpen = false;

          event.stopImmediatePropagation();
          $dropdownMenu.hide();
        }
      }

      /**
       * @returns {object} a reference to the dropdown menu container.
       */
      function getChildDropdownMenu () {
        return $element.find('.dropdown-menu:first');
      }

      /**
       * Hides the dropdown menu.
       */
      function hideDropdown () {
        var $dropdownMenu = getChildDropdownMenu();
        isOpen = false;

        $dropdownMenu.hide();
      }

      /**
       * Initializes the event listeners necessary to display or hide the dropdown menu.
       */
      function initEventListeners () {
        $toggleElement.on('click', toggleDropdown);
        $('body').on('keydown', closeDropdownOnEscapeKeyPressed);
        $(document).on('click', closeDropdownOnClickOutside);

        if (attrs.civicaseDropdownTrigger === 'hover') {
          $element.on('mouseenter', showDropdown);
          $element.on('mouseleave', hideDropdown);
        }
      }

      /**
       * Displays the dropdown menu.
       */
      function showDropdown () {
        var $dropdownMenu = getChildDropdownMenu();
        isOpen = true;

        $dropdownMenu.show();
      }

      /**
       * Removes all attached events from the DOM when the scope is destroyed.
       */
      function removeEventListenersOnScopeDestroy () {
        scope.$on('$destroy', function () {
          $toggleElement.unbind('click', toggleDropdown);
          $('body').unbind('keydown', closeDropdownOnEscapeKeyPressed);
          $(document).unbind('click', closeDropdownOnClickOutside);
          $element.unbind('mouseenter', showDropdown);
          $element.unbind('mouseleave', hideDropdown);
        });
      }

      /**
       * Toggles the dropdown menu visibility
       */
      function toggleDropdown () {
        isOpen ? hideDropdown() : showDropdown();
      }
    }
  });
})(CRM.$, angular);
