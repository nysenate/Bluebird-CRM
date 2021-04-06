/* global isMailing, verify */
// These variables are defined in civicrm/templates/CRM/Mailing/Form/InsertTokens.tpl

/**
 * This is not added inside an IIFE, because
 * We remove the crmLoad event before assigning it again. And for this to work,
 * the reference to the listener being removed needs to be the same.
 * But when the function is defined inside an IIFE, the function gets assigned
 * a new reference every time. So the event listener does not get removed.
 */
CRM['civicase-base'].tokentree = {};

(function ($, CiviCaseBase) {
  /**
   * @param {object} eventObj event object
   */
  CiviCaseBase.tokentree.onCrmLoad = function (eventObj) {
    // When opening the form in new tab, instead of modal
    // the tokens are initialised in core after crmLoad event is fired
    // (In civicrm/templates/CRM/Mailing/Form/InsertTokens.tpl)
    // Thats why, we also wait for document to be ready
    $(function () {
      var form = $(eventObj.target).find('form');

      initialiseTokenTree(form);
    });
  };

  $(document)
    .off('crmLoad', CiviCaseBase.tokentree.onCrmLoad)
    .on('crmLoad', CiviCaseBase.tokentree.onCrmLoad);

  /**
   * Collapse all tree elements
   */
  function collapseAll () {
    $('[has-children]').parent().siblings('.select2-result-sub').hide();
  }

  /**
   * Initialise Token Tree widget
   *
   * @param {object} form form element
   */
  function initialiseTokenTree (form) {
    var tokens = JSON.parse(CiviCaseBase.custom_token_tree);

    $('input.crm-token-selector', form)
      .crmSelect2({
        data: tokens,
        formatResult: formatOptions,
        formatSelection: formatOptions,
        placeholder: 'Tokens'
      })
      .off('select2-open').on('select2-open', collapseAll)
      .off('select2-selecting').on('select2-selecting', selectEventHandler);
  }

  /**
   * Select event handler
   *
   * @param {object} event event object
   */
  function selectEventHandler (event) {
    if (!event.choice.children) {
      insertTokenIntoTextBox.call(this, event);

      return;
    }
    toggleTreeElement(event);
  }

  /**
   * Copied from (In civicrm/templates/CRM/Mailing/Form/InsertTokens.tpl)
   * except the last line, which is added to stop the dropdown from closing
   * after selecting a value
   *
   * @param {object} event event object
   */
  function insertTokenIntoTextBox (event) {
    var token = event.choice.id;
    var field = $(this).data('field');
    if (field.indexOf('html') < 0) {
      field = textMsgID($(this));
    }
    CRM.wysiwyg.insert('#' + field, token);
    $(this).select2('val', '');
    if (isMailing) {
      verify();
    }

    event.preventDefault();
  }

  /**
   * @param {object} event event object
   */
  function toggleTreeElement (event) {
    var element = $('[data-token-select-id=' + event.choice.id + ']');
    var childElement = element.closest('.select2-result-label').siblings('.select2-result-sub');

    childElement.toggle();

    // Toggle the collapse/expand icon
    element.html(getDropdownElementText(event.choice, childElement.is(':visible')));

    event.preventDefault();
  }

  /**
   * Copied from (In civicrm/templates/CRM/Mailing/Form/InsertTokens.tpl)
   * Returns the ID of the Textbox where tokens needs to be inserted.
   *
   * @param {object} obj jquery element
   * @returns {string} field id
   */
  function textMsgID (obj) {
    var field;

    if (obj.parents().is('#sms')) {
      field = 'sms #' + obj.data('field');
    } else if (obj.parents().is('#email')) {
      field = 'email #' + obj.data('field');
    } else {
      field = obj.data('field');
    }

    return field;
  }

  /**
   * @param {object} item item
   * @returns {string} dropdown item markup
   */
  function formatOptions (item) {
    return getDropdownElementText(item, false);
  }

  /**
   * @param {object} item item
   * @param {object} isOpen if nested tree is shown
   * @returns {string} dropdown item markup
   */
  function getDropdownElementText (item, isOpen) {
    var icon = '';
    var hasChildrenIdentifier = '';

    if (item.children) {
      hasChildrenIdentifier = 'has-children ';
      if (isOpen) {
        icon = '<i class="fa fa-minus-square-o" style="margin-right: 5px;"></i>';
      } else {
        icon = '<i class="fa fa-plus-square-o" style="margin-right: 5px;"></i>';
      }
    }

    return '<span ' + hasChildrenIdentifier + 'data-token-select-id="' + item.id + '">' + icon + item.text + '</span>';
  }
})(CRM.$, CRM['civicase-base']);
