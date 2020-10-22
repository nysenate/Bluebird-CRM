(function (angular, $) {
  var module = angular.module('civicase');

  module.directive('civicaseDashboardTabsetAffix', function ($timeout) {
    return {
      link: civicaseDashboardTabsetAffixLink
    };

    function civicaseDashboardTabsetAffixLink (scope) {
      var $tabNavigation = $('.civicase__dashboard__tab-container ul.nav');
      var $civicrmMenu = $('#civicrm-menu');
      var $toolbarDrawer = $('#toolbar .toolbar-drawer');
      var $tabContainer = $('.civicase__dashboard__tab-container');
      var $parentContainer = $tabNavigation.parent();
      var parentOriginalTopPadding = parseInt($parentContainer.css('padding-top'), 10);
      var toolbarDrawerHeight = $toolbarDrawer.is(':visible') ? $toolbarDrawer.height() : 0;

      (function init () {
        affixTabNavigation();
        initDomEvents();
      })();

      /**
       * Affixes the tab nagivation menu so it sticks when the page scrolls.
       */
      function affixTabNavigation () {
        $timeout(function () {
          $tabNavigation.affix({
            offset: {
              top: $tabContainer.offset().top - (toolbarDrawerHeight + $civicrmMenu.height())
            }
          }).on('affixed.bs.affix', function () {
            $tabNavigation.css('top', $civicrmMenu.height() + toolbarDrawerHeight);
            $parentContainer.css('padding-top', parentOriginalTopPadding + $tabNavigation.height());
          }).on('affixed-top.bs.affix', function () {
            $parentContainer.css('padding-top', parentOriginalTopPadding);
            $tabNavigation.css('top', 'auto');
          });
        });
      }

      /**
       * Initializes DOM events:
       * - when the Drupal toolbar is toggled, it updates the navigation menu affix offset to
       * reflect the new height.
       */
      function initDomEvents () {
        $('#toolbar a.toggle').on('click', function () {
          toolbarDrawerHeight = $toolbarDrawer.is(':visible') ? $toolbarDrawer.height() : 0;

          $tabNavigation.data('bs.affix').options.offset.top = $tabContainer.offset().top - (toolbarDrawerHeight + $civicrmMenu.height());
        });
      }
    }
  });
})(angular, CRM.$);
