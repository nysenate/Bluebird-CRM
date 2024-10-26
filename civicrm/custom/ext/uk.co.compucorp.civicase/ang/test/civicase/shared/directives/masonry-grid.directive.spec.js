(function ($, _) {
  describe('Masonry Grid', function () {
    var $compile, $masonryGrid, $rootScope, $scope, $timeout;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_$compile_, _$rootScope_, _$timeout_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
    }));

    describe('sorting out the grid items', function () {
      var leftColumnItems, rightColumnItems;

      describe('when given a list of masonry grid item', function () {
        beforeEach(function () {
          initDirective();

          leftColumnItems = getGridItemsTextsForColumn(0);
          rightColumnItems = getGridItemsTextsForColumn(1);
        });

        it('has the Special, 2nd, and 4th element on the left column', function () {
          expect(leftColumnItems).toBe('Special, 2, 4');
        });

        it('has the 1st, and 3rd, and 5th element on the right column', function () {
          expect(rightColumnItems).toBe('1, 3, 5');
        });
      });

      describe('when the Special item is removed', function () {
        beforeEach(function () {
          initDirective();

          $scope.showSpecial = false;

          $rootScope.$digest();

          leftColumnItems = getGridItemsTextsForColumn(0);
          rightColumnItems = getGridItemsTextsForColumn(1);
        });

        it('moves the 1st, 3rd, and 5th element to the left column', function () {
          expect(leftColumnItems).toBe('1, 3, 5');
        });

        it('moves the 2nd and 4th element to the right column', function () {
          expect(rightColumnItems).toBe('2, 4');
        });
      });

      describe('when the Special item requests to be moved to the 4th position', function () {
        beforeEach(function () {
          initDirective({ specialPosition: 3 });

          leftColumnItems = getGridItemsTextsForColumn(0);
          rightColumnItems = getGridItemsTextsForColumn(1);
        });

        it('moves the 1st, 3rd, and 5th element to the left column', function () {
          expect(leftColumnItems).toBe('1, 3, 4');
        });

        it('moves the 2nd and 4th element to the right column', function () {
          expect(rightColumnItems).toBe('2, Special, 5');
        });
      });

      /**
       * Returns a string representation of the elements inside a given column.
       * Ex.: "1, 2, Special, 3"
       *
       * @param {number} columnIndex column index
       * @returns {string} text
       */
      function getGridItemsTextsForColumn (columnIndex) {
        return $masonryGrid.find('.civicase__masonry-grid__column')
          .eq(columnIndex)
          .find('civicase-masonry-grid-item')
          .map(function () {
            return $(this).text().trim();
          })
          .get()
          .join(', ');
      }
    });

    /**
     * Initialzes the masonry grid directive.
     *
     * @param {object} scopeValues scope values
     */
    function initDirective (scopeValues) {
      var defaultScopeValues = {
        showSpecial: true,
        specialPosition: 0
      };
      var html = `<civicase-masonry-grid>
        <civicase-masonry-grid-item ng-repeat="i in [1,2,3,4,5]">
          {{i}}
        </civicase-masonry-grid-item>
        <civicase-masonry-grid-item
          position="{{specialPosition}}"
          ng-if="showSpecial">
          Special
        </civicase-masonry-grid-item>
      </civicase-masonry-grid>`;
      $scope = $rootScope.$new();
      $scope = _.assign($scope, defaultScopeValues, scopeValues);
      $masonryGrid = $compile(html)($scope);

      $rootScope.$digest();
      $timeout.flush();
    }
  });
})(CRM.$, CRM._);
