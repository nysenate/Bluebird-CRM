(function (angular) {
  var module = angular.module('civicase');

  module.config(activityFormsConfiguration);

  /**
   * Configures the list of activity forms services that will be available
   * when displaying a form for a particular activity.
   *
   * @param {object} ActivityFormsProvider the activity forms provider.
   */
  function activityFormsConfiguration (ActivityFormsProvider) {
    var addActivityFormConfigs = [
      {
        name: 'AddCustomPathActivityForm',
        weight: 0
      },
      {
        name: 'AddActivityForm',
        weight: 1
      },
      {
        name: 'DraftPdfActivityForm',
        weight: 2
      },
      {
        name: 'DraftEmailActivityForm',
        weight: 3
      },
      {
        name: 'UpdateActivityForm',
        weight: 4
      },
      {
        name: 'ViewActivityForm',
        weight: 5
      }
    ];

    ActivityFormsProvider.addActivityForms(addActivityFormConfigs);
  }
})(angular);
