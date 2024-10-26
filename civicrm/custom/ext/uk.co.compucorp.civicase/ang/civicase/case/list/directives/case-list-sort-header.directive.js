(function (angular) {
  var module = angular.module('civicase');

  /**
   * Directive for the case list sortable headers
   */
  module.directive('civicaseCaseListSortHeader', function () {
    return {
      restrict: 'A',
      link: civicaseSortheaderLink
    };

    /**
     * Link function for civicaseSortheaderLink Directive
     *
     * @param {object} scope scope object
     * @param {object} element element
     * @param {object} attrs attributes
     */
    function civicaseSortheaderLink (scope, element, attrs) {
      (function init () {
        initiateSortFunctionality();
        scope.$watchCollection('sort', sortWatchHandler);
      }());

      /**
       * Initiate the sort functionality if the header is sortable
       */
      function initiateSortFunctionality () {
        if (scope.sort.sortable && attrs.civicaseCaseListSortHeader !== '') {
          element
            .addClass('civicase__case-list-sortable-header')
            .on('click', headerClickEventHandler);
        }
      }

      /**
       * Click event for the header
       * If the Clicked field is already selected, change the direction
       * Otherwise, set the new field and direction as ascending
       */
      function headerClickEventHandler () {
        scope.$apply(function () {
          if (scope.sort.field === attrs.civicaseCaseListSortHeader) {
            scope.changeSortDir();
          } else {
            scope.sort.field = attrs.civicaseCaseListSortHeader;
            scope.sort.dir = 'ASC';
          }
        });
      }

      /**
       * Watch event for the Sort property
       */
      function sortWatchHandler () {
        element.toggleClass('active', attrs.civicaseCaseListSortHeader === scope.sort.field);
        element.find('.civicase__case-list__header-toggle-sort').remove();

        if (attrs.civicaseCaseListSortHeader === scope.sort.field) {
          var direction = scope.sort.dir === 'ASC' ? 'up' : 'down';
          var sortIcon = '<i class="civicase__case-list__header-toggle-sort material-icons">arrow_' + direction + 'ward</i>';
          element.append(sortIcon);
        }
      }
    }
  });
})(angular);
