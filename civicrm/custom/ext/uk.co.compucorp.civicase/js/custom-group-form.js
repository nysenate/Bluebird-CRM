(function ($, _, caseEntityNames) {
  $(document).on('crmLoad', function () {
    var $multipleRecordsCheckboxRow = $('#is_multiple_row');
    var $styleSelectTabWithTableOption = $('select[name="style"] option[value="Tab with table"]');
    var $extendSelect = $('#extends_0');

    (function init () {
      hideShowElementsNotRelevantWhenExtendingCases();
      $extendSelect.on('change', hideShowElementsNotRelevantWhenExtendingCases);
    })();

    /**
     * This function will hide or show elements that are not relevant when extending
     * the Case entities. The elements we hide or show are:
     *
     * - Multiple Records Checkbox.
     * - Style Select's "Tab with table" option.
     *
     * We use a timeout to wait for the original core script to do its own changes
     * and to alter these after core is done.
     *
     * If the checkbox row was hidden we don't need to restore it since core does
     * this automatically, unlike the style option which we need to manually restore.
     */
    function hideShowElementsNotRelevantWhenExtendingCases () {
      setTimeout(function () {
        if (_.includes(caseEntityNames, $extendSelect.val())) {
          $multipleRecordsCheckboxRow.css('display', 'none');
          $styleSelectTabWithTableOption.hide();
        } else {
          $styleSelectTabWithTableOption.show();
        }
      }, 0);
    }
  });
})(CRM.$, CRM._, CRM.caseEntityNames);
