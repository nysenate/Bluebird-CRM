/* global SimpleBar */
/* eslint-disable no-new */
(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCustomScrollbar', function ($rootScope, $parse, $timeout) {
    return {
      restrict: 'A',
      link: civicaseCustomScrollbarLink
    };

    /**
     * Link function for civicaseCustomScrollbar
     *
     * @param {object} $scope scope object
     * @param {object} element element
     * @param {object} attrs attributes
     */
    function civicaseCustomScrollbarLink ($scope, element, attrs) {
      var simplebarScroller, options;
      var defaultOptions = {
        autoHide: false
      };

      if (attrs.scrollbarConfig) {
        options = _.defaults($parse(attrs.scrollbarConfig)(), defaultOptions);
      }

      (function init () {
        initScrollbar();
        initSubscribers();
      }());

      /**
       * Initiate scrollbar plugin
       */
      function initScrollbar () {
        simplebarScroller = new SimpleBar(element[0], options);
      }

      /**
       * Initiate Subscribers
       */
      function initSubscribers () {
        $rootScope.$on('civicase::custom-scrollbar::recalculate', recalculateSubscriber);
      }

      /**
       * Subscriber for 'civicase::custom-scrollbar::recalculate' event
       * Recalculate the positioning.
       */
      function recalculateSubscriber () {
        $timeout(function () {
          simplebarScroller.recalculate();
        });
      }
    }
  });
})(angular, CRM.$, CRM._);
