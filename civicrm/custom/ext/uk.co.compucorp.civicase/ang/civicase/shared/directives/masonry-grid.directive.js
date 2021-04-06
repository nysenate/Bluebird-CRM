(function (_, $, angular) {
  var module = angular.module('civicase');

  module.directive('civicaseMasonryGrid', function ($timeout) {
    return {
      restrict: 'E',
      controller: 'civicaseMasonryGridController',
      link: civicaseMasonryGridLink
    };

    /**
     * Masonry Grid link function.
     *
     * @param {object} $scope the directive's scope.
     * @param {object} $element a reference to the directive's element.
     * @param {object} attrs a map of attributes associated to the element.
     * @param {object} ctrl a reference to the directive's controller.
     */
    function civicaseMasonryGridLink ($scope, $element, attrs, ctrl) {
      var NO_OF_COLUMNS = 2;

      (function init () {
        $timeout(function () {
          appendMasonryColumns();
          arrangeGridItems();
          $scope.$on('civicaseMasonryGrid::updated', arrangeGridItems);
        });
      })();

      /**
       * Appends the masonry containers used for splitting the grid items.
       */
      function appendMasonryColumns () {
        _.times(NO_OF_COLUMNS).forEach(function () {
          $('<div></div>')
            .addClass('civicase__summary-tab-tile civicase__masonry-grid__column')
            .appendTo($element);
        });
      }

      /**
       * Sorts the grid items into one of the two grid containers.
       */
      function arrangeGridItems () {
        $element.find('civicase-masonry-grid-item').detach();

        ctrl.$gridItems.forEach(function ($gridItem, index) {
          var columnIndex = index % 2;
          var $column = $element.find('.civicase__masonry-grid__column').eq(columnIndex);

          $gridItem.appendTo($column);
        });
      }
    }
  });

  module.controller('civicaseMasonryGridController', function ($scope) {
    var vm = this;
    vm.$gridItems = [];

    /**
     * Adds an item element to the grid.
     *
     * @param {object} $gridItem a reference to the item element to be added.
     */
    vm.addGridItem = function ($gridItem) {
      removeDuplicatedGridItem($gridItem);
      vm.$gridItems.push($gridItem);
      $scope.$emit('civicaseMasonryGrid::updated');
    };

    /**
     * Adds an item element to the grid at the given index.
     *
     * @param {object} $gridItem a reference to the item element to be added.
     * @param {number} atIndex index
     */
    vm.addGridItemAt = function ($gridItem, atIndex) {
      removeDuplicatedGridItem($gridItem);
      vm.$gridItems.splice(atIndex, 0, $gridItem);
      $scope.$emit('civicaseMasonryGrid::updated');
    };

    /**
     * Removes the item element from the grid.
     *
     * @param {object} $gridItem a reference to the item element to be removed.
     */
    vm.removeGridItem = function ($gridItem) {
      _.remove(vm.$gridItems, $gridItem);
      $scope.$emit('civicaseMasonryGrid::updated');
    };

    /**
     * Removes any reference to the given grid item to avoid duplication.
     * This is done in case the grid item needs to be moved from one position to
     * another.
     *
     * @param {object} $gridItem a reference to the item element to be removed.
     */
    function removeDuplicatedGridItem ($gridItem) {
      _.remove(vm.$gridItems, $gridItem);
    }
  });

  module.directive('civicaseMasonryGridItem', function () {
    return {
      restrict: 'E',
      require: '^civicaseMasonryGrid',
      link: civicaseMasonryGridItemLink
    };

    /**
     * Masonry grid item link function.
     *
     * @param {object} $scope the directive's scope.
     * @param {object} $element a reference to the directive's element.
     * @param {object} attrs a map of attributes associated to the element.
     * @param {object} masonryGrid a reference to the parent masonry grid directive's controller.
     */
    function civicaseMasonryGridItemLink ($scope, $element, attrs, masonryGrid) {
      (function init () {
        if (attrs.position) {
          masonryGrid.addGridItemAt($element, attrs.position);
        } else {
          masonryGrid.addGridItem($element);
        }

        $scope.$on('$destroy', function () {
          masonryGrid.removeGridItem($element);
        });
      })();
    }
  });
})(CRM._, CRM.$, angular);
