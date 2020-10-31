(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseActivityCount', function ($timeout, $sce) {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/card/directives/case-activity-count.directive.html',
      link: civicaseCaseActivityCountLink
    };

    /**
     * Link function for civicaseCaseActivityCount
     *
     * @param {Object} scope
     * @param {Object} $elm
     */
    function civicaseCaseActivityCountLink (scope, $elm) {
      (function init () {
        initPopoverCalculation();
      })();

      /**
       * Detect the elements which needs to be moved to the tooltip
       *
       * @return {Array}
       */
      function detectElementsToBeMoved () {
        var parentWidth = $elm[0].offsetWidth;
        var $children = $elm.children();
        var elementsToBeMoved = [];

        _.each($children, function (child) {
          if ((child.offsetLeft + child.offsetWidth) > parentWidth) {
            elementsToBeMoved.push($(child));
          }
        });

        return elementsToBeMoved;
      }

      /**
       * Detect if tooltip needs to be shown
       *
       * @return {Boolean}
       */
      function detectIfToolTipIsNecessary () {
        var $element = $elm[0];

        return $element.scrollWidth > $element.offsetWidth;
      }

      /**
       * Hide the Moved elements
       *
       * @param {Array} elementsToBeMoved
       */
      function hideMovedElements (elementsToBeMoved) {
        _.each(elementsToBeMoved, function ($element) {
          $element.hide();
        });
      }

      /**
       * Initialise the Popover Calculation
       */
      function initPopoverCalculation () {
        scope.tooltipEnabled = false;

        $timeout(function () {
          if (detectIfToolTipIsNecessary()) {
            var elementsToBeMoved = detectElementsToBeMoved();

            moveElements(elementsToBeMoved);
            hideMovedElements(elementsToBeMoved);

            scope.tooltipEnabled = true;
          }
        });
      }

      /**
       * Move the sent elements to the tooltip
       *
       * @param {Array} elementsToBeMoved
       */
      function moveElements (elementsToBeMoved) {
        var dynamicTooltipContent = '';

        _.each(elementsToBeMoved, function ($element) {
          dynamicTooltipContent += $element.clone().html();
        });

        scope.dynamicTooltipContent = $sce.trustAsHtml(dynamicTooltipContent);
      }
    }
  });
})(angular, CRM.$, CRM._);
