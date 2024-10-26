(function (angular) {
  var module = angular.module('civicase');

  module.directive('civicaseContactIcon', function () {
    return {
      controller: 'civicaseContactIconController',
      templateUrl: '~/civicase/contact/directives/contact-icon.directive.html',
      scope: {
        autoCloseOtherPopovers: '<?',
        caseId: '<?',
        contactId: '<'
      }
    };
  });

  module.controller('civicaseContactIconController', function ($scope, ContactsCache) {
    (function init () {
      setContactIcon();
      initWatchers();
    })();

    /**
     * Init watchers
     */
    function initWatchers () {
      $scope.$on('civicase::contacts-cache::contacts-added', setContactIcon);
    }

    /**
     * Set Contact icon
     */
    function setContactIcon () {
      $scope.contactIcon = ContactsCache.getContactIconOf($scope.contactId);
    }
  });
})(angular);
