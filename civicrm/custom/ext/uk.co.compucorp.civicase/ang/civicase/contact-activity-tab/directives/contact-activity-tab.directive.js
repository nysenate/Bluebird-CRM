(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseContactActivityTab', function () {
    return {
      restrict: 'EA',
      controller: 'CivicaseContactActivityTabController',
      templateUrl: '~/civicase/contact-activity-tab/directives/contact-activity-tab.directive.html',
      scope: {}
    };
  });

  module.controller('CivicaseContactActivityTabController', CivicaseContactActivityTabController);

  /**
   * @param {object} $scope the controller scope
   * @param {object} Contact Contact global service
   */
  function CivicaseContactActivityTabController ($scope, Contact) {
    $scope.filters = { $contact_id: Contact.getCurrentContactID() };
    $scope.displayOptions = { include_case: false };
  }
})(angular, CRM.$, CRM._);
