(function (angular, configuration, civiCrmConfig) {
  var module = angular.module('civicase-base');

  module
    .constant('allowCaseLocks', configuration.allowCaseLocks)
    .constant('allowLinkedCasesTab', configuration.allowLinkedCasesTab)
    .constant('allowMultipleCaseClients', configuration.allowMultipleCaseClients)
    .constant('currentCaseCategory', configuration.currentCaseCategory)
    .constant('showFullContactNameOnActivityFeed', configuration.showFullContactNameOnActivityFeed)
    .constant('includeActivitiesForInvolvedContact', configuration.includeActivitiesForInvolvedContact)
    .constant('civicaseSingleCaseRolePerType', configuration.civicaseSingleCaseRolePerType)
    .constant('dateInputFormatValue', civiCrmConfig.dateInputFormat)
    .constant('loggedInContactId', civiCrmConfig.user_contact_id)
    .constant('webformsList', {
      isVisible: configuration.showWebformsListSeparately,
      buttonLabel: configuration.webformsDropdownButtonLabel
    });
})(angular, CRM['civicase-base'], CRM.config);
