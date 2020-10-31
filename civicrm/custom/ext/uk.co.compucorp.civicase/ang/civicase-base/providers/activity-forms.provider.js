(function (_, angular) {
  var module = angular.module('civicase-base');

  module.provider('ActivityForms', ActivityFormsProvider);

  /**
   * Activity Forms Provider.
   */
  function ActivityFormsProvider () {
    var activityForms = [];

    this.$get = $get;
    this.addActivityForms = addActivityForms;

    /**
     * Returns a `getActivityFormService` function that can be used to find
     * the right form service for the given activity.
     *
     * @param {object} $injector the angular injector service.
     * @returns {object} The Activity Forms service.
     */
    function $get ($injector) {
      var activityFormServices = _.chain(activityForms)
        .sortBy('weight')
        .map('name')
        .map(injectActivityFormService)
        .value();

      return {
        getActivityFormService: getActivityFormService
      };

      /**
       * @param {object} activity an activity object.
       * @param {object} [options={}] configuration options for the form.
       * @returns {object|undefined} the first activity form service that can handle
       *   the given activity.
       */
      function getActivityFormService (activity, options) {
        return _.find(activityFormServices, function (activityFormService) {
          return activityFormService && activityFormService
            .canHandleActivity(activity, options || {});
        });
      }

      /**
       * @param {string} activityFormServiceName the name of the activity form service.
       * @returns {object|null} the activity form service corresponding to the given name.
       */
      function injectActivityFormService (activityFormServiceName) {
        try {
          return $injector.get(activityFormServiceName);
        } catch (error) {
          return null;
        }
      }
    }

    /**
     * Adds the given activity form configurations to the provider.
     *
     * @param {activityFormConfig[]} activityFormConfigs a list of activity form names and weights.
     */
    function addActivityForms (activityFormConfigs) {
      activityForms = activityForms.concat(activityFormConfigs);
    }
  }

  /**
   * @typedef {object} activityFormConfig
   * @property {string} name the activity form service name.
   * @property {number} weight the priority value compared to other services.
   */
})(CRM._, angular);
