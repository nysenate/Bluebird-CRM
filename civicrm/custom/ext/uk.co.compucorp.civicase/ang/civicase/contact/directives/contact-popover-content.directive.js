(function (angular) {
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
   * @param {Function} civicaseCrmUrl crm url service.
   */
  function civicaseContactPopoverContentController ($scope, ContactsCache,
    civicaseCrmUrl) {
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

      return civicaseCrmUrl('civicrm/activity/email/add', emailUrlParameters);
    }
  }
})(angular);
