describe('civicaseCaseListSortHeader directive', function () {
  var element, $compile, $rootScope, scope;

  beforeEach(module('civicase'));

  beforeEach(inject(function (_$compile_, _$rootScope_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    scope = $rootScope.$new();
  }));

  describe('basic tests', function () {
    var header;

    describe('when sortable is false', function () {
      beforeEach(function () {
        scope.sort = { field: '', dir: '', sortable: false };
        header = {
          display_type: 'activity_card',
          label: 'Next Activity',
          name: 'next_activity',
          sort: 'next_activity'
        };

        compileDirective(scope, header);
      });

      it('does not make the header sortable', function () {
        expect(element.hasClass('civicase__case-list-sortable-header')).not.toBe(true);
      });
    });
    describe('when header is blank', function () {
      beforeEach(function () {
        scope.sort = { field: '', dir: '', sortable: true };
        header = '';
        compileDirective(scope, header);
      });

      it('does not make the header sortable', function () {
        expect(element.hasClass('civicase__case-list-sortable-header')).not.toBe(true);
      });
    });
    describe('when sortable is true and header is not blank', function () {
      describe('basic tests', function () {
        beforeEach(function () {
          scope.sort = { field: '', dir: '', sortable: true };
          header = 'next_activity';

          compileDirective(scope, header);
        });

        it('makes the header sortable', function () {
          expect(element.hasClass('civicase__case-list-sortable-header')).toBe(true);
        });
      });

      describe('headerClickEventHandler', function () {
        describe('when the clicked header is already sorted', function () {
          beforeEach(function () {
            header = 'next_activity';
            scope.sort = { field: header, dir: '', sortable: true };
            scope.changeSortDir = jasmine.createSpy('changeSortDir');

            compileDirective(scope, header);
            element.trigger('click');
          });

          it('changes the sorting direction', function () {
            expect(scope.changeSortDir).toHaveBeenCalled();
          });
        });

        describe('when the clicked header is not already sorted', function () {
          beforeEach(function () {
            header = 'next_activity';
            scope.sort = { field: 'not_next_activity', dir: '', sortable: true };
            scope.changeSortDir = jasmine.createSpy('changeSortDir');

            compileDirective(scope, header);
            element.trigger('click');
          });

          it('sorts the clicked header', function () {
            expect(scope.sort.field).toBe(header);
          });

          it('sorts the clicked header in ascending order', function () {
            expect(scope.sort.dir).toBe('ASC');
          });
        });
      });

      describe('sortWatchHandler', function () {
        beforeEach(function () {
          header = 'next_activity';
          scope.sort = { field: header, dir: '', sortable: true };
          scope.changeSortDir = jasmine.createSpy('changeSortDir');

          compileDirective(scope, header);
        });

        describe('when the clicked header is already sorted', function () {
          beforeEach(function () {
            scope.sort = { field: header, dir: 'ASC', sortable: true };
            scope.$digest();
          });

          it('changes the sorting icon direction', function () {
            expect(element.hasClass('active')).toBe(true);
          });

          it('removes the sorting icon before adding a new one', function () {
            expect(element.find('.civicase__case-list__header-toggle-sort').length).toBe(1);
          });

          it('adds the sorting icon before adding a new one', function () {
            expect(element.html()).toContain('civicase__case-list__header-toggle-sort');
          });
        });

        describe('when the sorting direction is ascending', function () {
          beforeEach(function () {
            scope.sort = { field: header, dir: 'ASC', sortable: true };
            scope.$digest();
          });

          it('adds the upward sorting icon', function () {
            expect(element.html()).toContain('arrow_upward');
          });
        });

        describe('when the sorting direction is decsending', function () {
          beforeEach(function () {
            scope.sort = { field: header, dir: 'DESC', sortable: true };
            scope.$digest();
          });

          it('adds the upward sorting icon', function () {
            expect(element.html()).toContain('arrow_downward');
          });
        });
      });
    });
  });

  /**
   * Compiles the directive
   *
   * @param {object} scope scope object
   * @param {string} header header
   */
  function compileDirective (scope, header) {
    element = $compile(angular.element('<div civicase-case-list-sort-header=' + header + '></div>'))(scope);
  }
});
