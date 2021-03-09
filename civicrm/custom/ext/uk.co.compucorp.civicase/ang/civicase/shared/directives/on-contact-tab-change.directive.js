(function ($, angular) {
  var module = angular.module('civicase');

  module.directive('civicaseOnContactTabChange', function (UrlParameters) {
    return {
      restrict: 'A',
      link: civicaseOnContactTabChangeLink,
      scope: {
        onContactTabChange: '&civicaseOnContactTabChange'
      }
    };

    /**
     * Listens for contact tab change and triggers the handler
     * passed down to this directive. It provides the URL and URL
     * parameters (as objects) for the new tab.
     *
     * @param {object} $scope a reference to the directive's scope.
     */
    function civicaseOnContactTabChangeLink ($scope) {
      var contactTabs = $('.page-civicrm-contact #mainTabContainer');

      (function init () {
        contactTabs.on('tabsactivate', listenToContactTabChange);
      })();

      /**
       * Handles the contact tab change.
       *
       * @param {document#event:tabsactivate} event the tab change event reference.
       * @param {object} uiTab a UI Tab event object which contains a reference to the new
       *   and previous tabs.
       */
      function listenToContactTabChange (event, uiTab) {
        var tabUrl = $(uiTab.newTab).find('a').attr('href');
        var urlParams = UrlParameters.parse(tabUrl);

        $scope.$apply(function () {
          $scope.onContactTabChange({
            $tabUrl: tabUrl,
            $tabUrlParams: urlParams
          });
        });
      }
    }
  });
})(CRM.$, angular);
