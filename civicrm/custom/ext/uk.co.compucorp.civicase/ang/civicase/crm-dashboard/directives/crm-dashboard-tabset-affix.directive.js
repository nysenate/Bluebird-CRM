(function (angular, $) {
  var module = angular.module('civicase');

  module.directive('civicaseCrmDashboardTabsetAffix', function ($timeout) {
    return {
      link: civicaseDashboardTabsetAffixLink
    };

    /**
     * Civicase Dashboard Tabset Affix Link
     *
     * @param {object} scope object
     */
    function civicaseDashboardTabsetAffixLink (scope) {
      var $tabNavigation = $('.civicase__tabs');
      var $civicrmMenu = $('#civicrm-menu');
      var $toolbarDrawer = $('#toolbar .toolbar-drawer');
      var $tabContainer = $('.civicase__crm-dashboard');
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
          })
            .on('affixed.bs.affix', handleTabNavigationAffix)
            .on('affixed-top.bs.affix', handleTabNavigationRestore);
        });
      }

      /**
       * Handles the changes that happen when the tab navigation is affixed.
       * It adds top spacing to both the navigation menu and the dashboard to
       * properly display the affixed elements.
       */
      function handleTabNavigationAffix () {
        var $adminMenu = $('#admin-menu');
        var adminMenuHeight = $adminMenu.is(':visible')
          ? $adminMenu.height()
          : 0;
        var tabNavigationTopPosition = $civicrmMenu.height() + toolbarDrawerHeight +
          adminMenuHeight;
        var parentContainerPaddingTop = $tabNavigation.height() +
          parentOriginalTopPadding + adminMenuHeight;

        $tabNavigation.css('top', tabNavigationTopPosition);
        $parentContainer.css('padding-top', parentContainerPaddingTop);
      }

      /**
       * It restore the navigation tabs and the dashboard container to their
       * original states.
       */
      function handleTabNavigationRestore () {
        $parentContainer.css('padding-top', parentOriginalTopPadding);
        $tabNavigation.css('top', 'auto');
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
