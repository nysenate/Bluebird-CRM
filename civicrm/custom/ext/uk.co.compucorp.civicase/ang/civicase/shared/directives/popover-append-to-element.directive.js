(function ($, angular) {
  var module = angular.module('civicase');

  module.directive('civicasePopoverAppendToElement', function ($timeout) {
    return {
      restrict: 'A',
      link: civicasePopoverAppendToElementLink
    };

    function civicasePopoverAppendToElementLink ($scope, $element, attrs) {
      (function init () {
        $scope.$on('$includeContentLoaded', appendPopoverToElement);
      })();

      /**
       * It appends the popover element to the element defined in the directive's attribute.
       * This function is executed as soon as the popover opens.
       */
      function appendPopoverToElement () {
        var $popover = $element.next();

        // This is executed one step after the original popover positioning:
        $timeout(function () {
          var position = $popover.offset();
          $popover.appendTo(attrs.civicasePopoverAppendToElement);
          $popover.offset(position);
          $popover.show();
        }, 1, false);
      }
    }
  });
})(CRM.$, angular);
