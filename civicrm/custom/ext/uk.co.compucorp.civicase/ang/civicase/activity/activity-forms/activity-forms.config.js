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
        name: 'EmailActivityForm',
        weight: 2
      },
      {
        name: 'DraftPdfActivityForm',
        weight: 3
      },
      {
        name: 'DraftEmailActivityForm',
        weight: 4
      },
      {
        name: 'UpdateActivityForm',
        weight: 5
      },
      {
        name: 'ViewActivityForm',
        weight: 6
      }
    ];

    ActivityFormsProvider.addActivityForms(addActivityFormConfigs);
  }
})(angular);
