(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseTooltip', function ($timeout) {
    return {
      restrict: 'E',
      replace: true,
      transclude: true,
      templateUrl: '~/civicase/shared/directives/tooltip.directive.html',
      link: caseTooltipLink,
      scope: {
        tooltipText: '='
      }
    };

    /**
     * Link function for caseTooltip
     *
     * @param {Object} scope
     * @param {Object} $elm
     */
    function caseTooltipLink (scope, $elm) {
      scope.tooltipEnabled = false;

      (function init () {
        $timeout(function () {
          var textElement = $elm.find('.civicase__tooltip__ellipsis')[0];

          if (textElement.scrollWidth > textElement.offsetWidth) {
            scope.tooltipEnabled = true;
          }
        });
      })();
    }
  });
})(angular, CRM.$, CRM._);
