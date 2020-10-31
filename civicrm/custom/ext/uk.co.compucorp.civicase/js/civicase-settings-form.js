(function ($) {
  $(document).on('crmLoad', function () {
    var $allowWebformCheckBoxes = $('.civicase__settings__allow-webform');
    var $webformUrlFields = $('.civicase__settings__webform-url');

    (function init () {
      showHideWebformUrlFields();
      $allowWebformCheckBoxes.change(showHideRelatedFormUrlField);
    })();

    /**
     * Toggles the visibility of all the webform URL fields.
     */
    function showHideWebformUrlFields () {
      $allowWebformCheckBoxes.filter(':checked')
        .each(showHideRelatedFormUrlField);
    }

    /**
     * Toggles the visibility of a webform URL field that is related to the
     * referenced "Allow Webform" field. `$(this)` refers to the "Allow Webform"
     * field.
     */
    function showHideRelatedFormUrlField () {
      var isAllowed = $(this).val() === '1';
      var caseCategoryName = $(this).attr('data-case-category-name');
      var $relatedWebformUrlFieldContainer = $webformUrlFields
        .filter('[data-case-category-name="' + caseCategoryName + '"]')
        .parents('tr');

      isAllowed
        ? $relatedWebformUrlFieldContainer.show()
        : $relatedWebformUrlFieldContainer.hide();
    }
  });
})(CRM.$);
