(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.service('removeDatePickerHrefs', function ($timeout) {
    var HREFS_SELECTOR = '[data-handler="selectDay"] a';

    return removeDatePickerHrefs;

    /**
     * Removes HREF attribtues from anchor elements defined in calendar inputs.
     * This is done to avoid switching the angular route's path unintentionally.
     */
    function removeDatePickerHrefs () {
      // Note: the last argument is always the UI Object
      var uiObject = _.last(arguments);

      $timeout(function () {
        uiObject.dpDiv
          .find(HREFS_SELECTOR)
          .removeAttr('href');
      });
    }
  });
})(angular, CRM.$, CRM._);
