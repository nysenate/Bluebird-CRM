(function () {
  var module = angular.module('civicase-base');

  /**
   * This directive can be attached to input elements and will trigger an API
   * request after the model changes.
   *
   * It also makes the model change trigger happen on blur.
   */
  module.directive('civicaseSendToApiOnChange', function ($q, $parse, civicaseCrmApi,
    crmStatus, crmThrottle) {
    return {
      restrict: 'A',
      link: civicaseSendToApiOnChangeLink,
      require: ['ngModel']
    };

    /**
     * Send To Api On Change linking function.
     *
     * @param {object} scope The directive's scope.
     * @param {object} element The directive's attached element.
     * @param {object} attributes List of attributes associated to the element.
     * @param {object[]} controllers List of controllers required by the directive.
     */
    function civicaseSendToApiOnChangeLink (scope, element, attributes, controllers) {
      var model = controllers[0];

      (function init () {
        model.isSaving = false;

        updateModelChangeBehaviour();
        model.$viewChangeListeners.push(sendModelDataToApi);
      })();

      /**
       * Sends the data stored in the "data-api-data" attribute to the CiviCRM
       * API. It marks the model as saving, displays a saving status and when
       * done it marks the model as not saving, displays a saved status and
       * triggers the function defined in "data-on-api-data-sent".
       */
      function sendModelDataToApi () {
        if (!model.$valid) {
          return;
        }

        var params = $parse(attributes.apiData)(scope);

        model.isSaving = true;

        crmStatus(
          {
            start: 'Saving',
            success: 'Saved'
          },
          crmThrottle(function () {
            return civicaseCrmApi(params);
          })
        )
          .then(function () {
            $parse(attributes.onApiDataSent)(scope);
          })
          .finally(function () {
            model.isSaving = false;
          });
      }

      /**
       * Updates the model change behaviour so it triggers on blur or 1 second
       * after changing.
       */
      function updateModelChangeBehaviour () {
        model.$$setOptions({
          updateOn: 'blur change',
          debounce: {
            blur: 0,
            change: 1000
          }
        });
      }
    }
  });
})(angular);
