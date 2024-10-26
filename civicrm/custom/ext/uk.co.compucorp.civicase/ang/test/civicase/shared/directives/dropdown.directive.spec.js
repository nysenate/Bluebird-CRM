(($) => {
  describe('Dropdown', () => {
    var $compile, $rootScope, dropdowns, dropdownContainer, scope;

    beforeEach(module('civicase'));

    beforeEach(inject((_$compile_, _$rootScope_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
    }));

    afterEach(() => {
      dropdownContainer.remove();
    });

    describe('opening the dropdown', () => {
      beforeEach(() => {
        initDirective();
      });

      describe('when clicking on the toggle element', () => {
        beforeEach(() => {
          dropdowns.parent.find('[civicase-dropdown-toggle]:first').click();
        });

        it('displays the dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(true);
        });
      });

      describe('when opening the parent menu and then clicking the child toggle element', () => {
        beforeEach(() => {
          dropdowns.parent.find('[civicase-dropdown-toggle]:first').click();
          dropdowns.child.find('[civicase-dropdown-toggle]:first').click();
        });

        it('displays the child dropdown menu', () => {
          expect(dropdowns.child.find('.dropdown-menu:first').is(':visible')).toBe(true);
        });
      });
    });

    describe('closing the dropdown', () => {
      beforeEach(() => {
        initDirective();
      });

      beforeEach(() => {
        dropdowns.parent.find('[civicase-dropdown-toggle]:first').click();
      });

      describe('when the dropdown is open and the toggle element is clicked again', () => {
        beforeEach(() => {
          dropdowns.parent.find('[civicase-dropdown-toggle]:first').click();
        });

        it('hides the dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(false);
        });
      });

      describe('when the dropdown is open and an element outside of the dropdown is clicked', () => {
        beforeEach(() => {
          $('body').click();
        });

        it('hides the dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(false);
        });
      });

      describe('when pressing the Escape key', () => {
        beforeEach(() => {
          var escapeKeydownEven = new window.KeyboardEvent('keydown', { key: 'Escape' });

          $('body')[0].dispatchEvent(escapeKeydownEven);
        });

        it('hides the child dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(false);
        });
      });

      describe('closing the child dropdown', () => {
        beforeEach(() => {
          dropdowns.child.find('[civicase-dropdown-toggle]:first').click();
        });

        describe('when the child toggle element is clicked', () => {
          beforeEach(() => {
            dropdowns.child.find('[civicase-dropdown-toggle]:first').click();
          });

          it('hides the child dropdown menu', () => {
            expect(dropdowns.child.find('.dropdown-menu:first').is(':visible')).toBe(false);
          });

          it('keeps the parent dropdown menu visible', () => {
            expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(true);
          });
        });

        describe('when pressing the Escape key', () => {
          beforeEach(() => {
            var escapeKeydownEven = new window.KeyboardEvent('keydown', { key: 'Escape' });

            $('body')[0].dispatchEvent(escapeKeydownEven);
          });

          it('hides the child dropdown menu', () => {
            expect(dropdowns.child.find('.dropdown-menu:first').is(':visible')).toBe(false);
          });

          it('keeps the parent dropdown menu visible', () => {
            expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(true);
          });
        });
      });
    });

    describe('when the dropdown menu is triggered by mouse events', () => {
      beforeEach(() => {
        initDirective({ trigger: 'hover' });
      });

      describe('when the mouse passes over the dropdown element', () => {
        beforeEach(() => {
          dispatchMouseEvent('mouseenter');
        });

        it('displays the dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(true);
        });
      });

      describe('when the mouse passes over and then leaves the dropdown element', () => {
        beforeEach(() => {
          dispatchMouseEvent('mouseenter');
          dispatchMouseEvent('mouseleave');
        });

        it('hides the dropdown menu', () => {
          expect(dropdowns.parent.find('.dropdown-menu:first').is(':visible')).toBe(false);
        });
      });

      /**
       * Dispatches the given event type to the dropdown toggle element.
       *
       * @param {string} eventType the mouse event type that is going to be dispatched to the element.
       */
      function dispatchMouseEvent (eventType) {
        var event = $.Event(eventType);

        dropdowns.parent.find('[civicase-dropdown-toggle]:first').trigger(event);
      }
    });

    /**
     * Initializes the dropdown directive, stores a reference to the dropdown element
     * and scope.
     *
     * @param {object} options a list of configurations to pass to the dropdown directive.
     */
    function initDirective (options) {
      var defaultOptions = { trigger: 'click' };
      options = $.extend({}, defaultOptions, options);

      var html = `<span civicase-dropdown name="parent-dropdown" civicase-dropdown-trigger="${options.trigger}">
        <button type="button" civicase-dropdown-toggle>Open dropdown</button>
        <ul class="dropdown-menu">
          <li><input /></li>
          <li><a href>Option 1</a></li>
          <li><a href>Option 2</a></li>
          <li><button type="button">Option 3</button></li>
          <li civicase-dropdown name="child-dropdown">
            <a href civicase-dropdown-toggle>Open nested dropdown</a>
            <ul class="dropdown-menu">
              <li><a href>Option 1</a></li>
              <li><a href>Option 2</a></li>
              <li><a href>Option 3</a></li>
            </ul>
          </li>
        </ul>
      </span>`;
      scope = $rootScope.$new();
      dropdownContainer = $compile(html)(scope);
      dropdowns = {
        parent: dropdownContainer,
        child: dropdownContainer.find('[name="child-dropdown"]')
      };

      dropdownContainer.find('.dropdown-menu').hide();
      dropdownContainer.appendTo('body');
      $rootScope.$digest();
    }
  });
})(CRM.$);
