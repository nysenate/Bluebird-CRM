(function ($, crmHelp) {
  $(document).on('crmLoad', function () {
    var $radioButtonsThatToggleVisibility = $('[data-toggles-visibility-for]');
    var $multiCaseClient = $('#civicaseAllowMultipleClients');
    var $singleCaseRoleParent = $('.crm-mail-form-block-civicaseSingleCaseRolePerType');
    var $singleCaseRole = $('#civicaseSingleCaseRolePerType_civicaseSingleCaseRolePerType');
    var $helpTextIcons = $('#crm-main-content-wrapper .helpicon');

    (function init () {
      displayHelpTextMessagesOnClick();
      toggleVisibilityOnRadioButtonChange();
      toggleVisibilityForSingleCaseRole();

      $radioButtonsThatToggleVisibility.change(toggleVisibilityOnRadioButtonChange);
      if ($multiCaseClient.length) {
        $multiCaseClient.change(toggleVisibilityForSingleCaseRole);
      }
    })();

    /**
     * Handles the display of help text messages associated with setting fields.
     *
     * Fixes an issue in core where help texts are not properly displayed.
     */
    function displayHelpTextMessagesOnClick () {
      $helpTextIcons.each((index, helpTextIconReference) => {
        var $helpTextIcon = $(helpTextIconReference);
        var $field = $helpTextIcon.siblings('[data-help-text]');
        var helpText = $field.attr('data-help-text');
        var fieldName = $field.attr('name');

        $helpTextIcon.removeAttr('onclick');
        $helpTextIcon.on('click', displayHelpTextMessage);

        /**
         * Displays the text message for the field.
         *
         * @param {object} event DOM Event.
         */
        function displayHelpTextMessage (event) {
          event.preventDefault();

          crmHelp(helpText, {
            id: fieldName + '-id',
            file: 'CRM/Admin/Form/Setting/Case'
          });
        }
      });
    }

    /**
     * Toggles the visibility of civicase settings fields based on radio button values.
     */
    function toggleVisibilityOnRadioButtonChange () {
      $radioButtonsThatToggleVisibility.filter(':checked')
        .each(function () {
          var $element = $(this);
          var isAllowed = $element.val() === '1';
          var $elementToToggle =
            $('.' + $element.data('toggles-visibility-for')).parents('tr');

          isAllowed ? $elementToToggle.show() : $elementToToggle.hide();
        });
    }

    /**
     * Toggles the visibility of single case role.
     */
    function toggleVisibilityForSingleCaseRole () {
      var $defaultMultiCaseClientValue = $singleCaseRole.attr('defaultMultipleCaseClient')
        ? $singleCaseRole.attr('defaultMultipleCaseClient')
        : '0';
      $multiCaseClient.val() === '0' ||
      ($multiCaseClient.val().toLowerCase() === 'default' && $defaultMultiCaseClientValue === '0')
        ? showSingleCaseRole()
        : hideSingleCaseRole();
    }

    /**
     * Shows single case role.
     */
    function showSingleCaseRole () {
      if ($singleCaseRoleParent.length) {
        $singleCaseRoleParent.show();
      }
    }

    /**
     * Hides single case role.
     */
    function hideSingleCaseRole () {
      if ($singleCaseRoleParent.length) {
        $singleCaseRoleParent.hide();
      }
      if ($singleCaseRole.length) {
        $singleCaseRole.prop('checked', false);
      }
    }
  });
})(CRM.$, CRM.help);
