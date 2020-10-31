(function (angular, getCrmUrl) {
  var module = angular.module('civicase');

  module.directive('civicaseContactPopoverContent', function () {
    return {
      controller: 'civicaseContactPopoverContentController',
      templateUrl: '~/civicase/contact/directives/contact-popover-content.directive.html',
      scope: {
        caseId: '<?',
        contactId: '<'
      }
    };
  });

  module.controller('civicaseContactPopoverContentController', civicaseContactPopoverContentController);

  /**
   * Contact Popover Content directive's controller.
   *
   * @param {object} $scope Scope object.
   * @param {object} ContactsCache Contacts Cache service reference.
   */
  function civicaseContactPopoverContentController ($scope, ContactsCache) {
    $scope.contact = ContactsCache.getCachedContact($scope.contactId);
    $scope.getEmailUrl = getEmailUrl;

    /**
     * @returns {string} The URL for composing an email for the current contact.
     */
    function getEmailUrl () {
      var emailUrlParameters = {
        action: 'add',
        cid: $scope.contactId,
        reset: 1
      };

      if ($scope.caseId) {
        emailUrlParameters.caseid = $scope.caseId;
      }

      return getCrmUrl('civicrm/activity/email/add', emailUrlParameters);
    }
  }
})(angular, CRM.url);
