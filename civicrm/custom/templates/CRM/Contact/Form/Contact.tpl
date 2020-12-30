{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* This form is for Contact Add/Edit interface *}
{if $addBlock}
  {include file="CRM/Contact/Form/Edit/$blockName.tpl"}
{else}
  {if $contactId}
    {include file="CRM/Contact/Form/Edit/Lock.tpl"}
  {/if}
  <div class="crm-form-block crm-search-form-block">
    {*NYSS - remove
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href='{crmURL p="civicrm/admin/setting/preferences/display" q="reset=1"}' title="{ts}Click here to configure the panes.{/ts}"><i class="crm-i fa-wrench" aria-hidden="true"></i></a>
    {/if}
    *}
    <span style="float:right;"><a href="#expand" id="expand">{ts}Expand all tabs{/ts}</a></span>
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <div class="crm-accordion-wrapper crm-contactDetails-accordion">
      <div class="crm-accordion-header">
        {ts}Contact Details{/ts}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body" id="contactDetails">
    <table>
      <tr>
        <td>
        {include file="CRM/Contact/Form/Edit/$contactType.tpl"}
        <span class="crm-button crm-button_qf_Contact_refresh_dedupe">
            {$form._qf_Contact_refresh_dedupe.html}
        </span>
        </td>
      </tr>
      <tr>
        <td>
        {foreach from = $editOptions item ="title" key="name"}
        {if $name eq "Address"}
                {include file="CRM/Contact/Form/Edit/$name.tpl"}
        {/if}
        {/foreach}
        </td>
      </tr>
       
      <tr>
        <td>
          <div class="subHeader">Communication Details</div>
        </td>
      </tr>
      <tr>
        <td>
          <table class="crm-section contact_information-section form-layout-compressed">
            {foreach from=$blocks item="label" key="block"}
              {include file="CRM/Contact/Form/Edit/$block.tpl"}
            {/foreach}
          </table>
        </td>
      </tr>
        
      {if $contactType eq "Individual"}
      <tr>
        <td>
            <div class="subHeader">Employment</div>
        </td>
      </tr>
      <tr>
        <td>
          <table class="form-layout-compressed individual-contact-details">
          <tr>
            <td>
              {$form.employer_id.label}&nbsp;{help id="id-current-employer" file="CRM/Contact/Form/Contact.hlp"}<br />
              {$form.employer_id.html}
            </td>
            <td>
              {$form.job_title.label}<br />
              {$form.job_title.html}
            </td>
          </tr>
          </table>
        </td>
      </tr>
    {/if}
    </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{*NYSS manually insert indiv custom fields so we can control layout/eliminate dups*}
{if $contactType eq "Individual"}
<div class="crm-accordion-wrapper crm-address-accordion crm-accordion-open">
  <div class="crm-accordion-header">
      Additional Constituent Information
  </div><!-- /.crm-accordion-header -->

  <div id="customData1" class="crm-accordion-body">
    <table class="form-layout-compressed">
      <tr class="custom_field-row">
        <td class="html-adjust" width="20%">
          {assign var='custom_18' value=$groupTree.1.fields.18.element_name}
          {$form.$custom_18.label}<br />
          {$form.$custom_18.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_18}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
        </td>
        <td class="html-adjust" width="20%">
          {assign var='custom_17' value=$groupTree.1.fields.17.element_name}
          {$form.$custom_17.label}<br />
          {$form.$custom_17.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_17}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
        </td>
        <td class="html-adjust" width="60%">
          {assign var='custom_19' value=$groupTree.1.fields.19.element_name}
          {$form.$custom_19.label}<br />
          {$form.$custom_19.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_19}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
        </td>
      </tr>
      <tr class="custom_field-row">
        <td class="html-adjust">
          {assign var='custom_16' value=$groupTree.1.fields.16.element_name}
          {$form.$custom_16.label}<br />
          {$form.$custom_16.html}
        </td>
        <td class="html-adjust">
          {assign var='custom_21' value=$groupTree.1.fields.21.element_name}
          {$form.$custom_21.label}<br />
          {$form.$custom_21.html}
        </td>
        <td class="html-adjust" rowspan="2">
          {assign var='custom_20' value=$groupTree.1.fields.20.element_name}
          {$form.$custom_20.label}<br />
          {$form.$custom_20.html}
        </td>
      </tr>
      <tr class="custom_field-row">
        <td class="html-adjust">
          {assign var='custom_23' value=$groupTree.1.fields.23.element_name}
          {$form.$custom_23.label}<br />
          {$form.$custom_23.html}
        </td>
        <td class="html-adjust">
          {assign var='custom_24' value=$groupTree.1.fields.24.element_name}
          {$form.$custom_24.label}<br />
          {$form.$custom_24.html}
        </td>
      </tr>
    </table>
  </div>
</div>
{/if}

    {foreach from = $editOptions item = "title" key="name"}
      {if $name eq 'CustomData' }
        <div id='customData'>{include file="CRM/Contact/Form/Edit/CustomData.tpl"}</div>
      {elseif $name neq "Address"}
        {include file="CRM/Contact/Form/Edit/$name.tpl"}
      {/if}
    {/foreach}
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
  {literal}

  <script type="text/javascript" >
  CRM.$(function($) {
    var $form = $("form.{/literal}{$form.formClass}{literal}"),
      action = {/literal}{$action|intval}{literal},
      cid = {/literal}{$contactId|intval}{literal},
      _ = CRM._;

    $('.crm-accordion-body').each( function() {
      //remove tab which doesn't have any element
      if ( ! $.trim( $(this).text() ) ) {
        ele     = $(this);
        prevEle = $(this).prev();
        $(ele).remove();
        $(prevEle).remove();
      }
      //open tab if form rule throws error
      if ( $(this).children().find('span.crm-error').text().length > 0 ) {
        $(this).parents('.collapsed').crmAccordionToggle();
      }
    });
    if (action === 2) {
      $('.crm-accordion-wrapper').not('.crm-accordion-wrapper .crm-accordion-wrapper').each(function() {
        highlightTabs(this);

        //NYSS 1748 call validate plugin
        cj("#Contact").validate();
      });
      $('#crm-container').on('change click', '.crm-accordion-body :input, .crm-accordion-body a', function() {
        highlightTabs($(this).parents('.crm-accordion-wrapper'));
      });
    }
    function highlightTabs(tab) {
      //highlight the tab having data inside.
      $('.crm-accordion-body :input', tab).each( function() {
        var active = false;
          switch($(this).prop('type')) {
            case 'checkbox':
            case 'radio':
              if($(this).is(':checked') && !$(this).is('[id$=IsPrimary],[id$=IsBilling]')) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;

            case 'text':
            case 'textarea':
              if($(this).val()) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;

            case 'select-one':
            case 'select-multiple':
              if($(this).val() && $('option[value=""]', this).length > 0) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;

            case 'file':
              if($(this).next().html()) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;
          }
          $('.crm-accordion-header:first', tab).removeClass('active');
      });
    }

    $('a#expand').click( function() {
      if( $(this).attr('href') == '#expand') {
        var message = {/literal}"{ts escape='js'}Collapse all tabs{/ts}"{literal};
        $(this).attr('href', '#collapse');
        $('.crm-accordion-wrapper.collapsed').crmAccordionToggle();
      }
      else {
        var message = {/literal}"{ts escape='js'}Expand all tabs{/ts}"{literal};
        $('.crm-accordion-wrapper:not(.collapsed)').crmAccordionToggle();
        $(this).attr('href', '#expand');
      }
      $(this).html(message);
      return false;
    });

    $('.customDataPresent').change(function() {
      var values = $("#contact_sub_type").val();
      CRM.buildCustomData({/literal}"{$contactType}"{literal}, values).one('crmLoad', function() {
        highlightTabs(this);
        loadMultiRecordFields(values);
      });
    });

    function loadMultiRecordFields(subTypeValues) {
      if (subTypeValues === false) {
        subTypeValues = null;
      }
      else if (!subTypeValues) {
        subTypeValues = {/literal}"{$paramSubType}"{literal};
      }
      function loadNextRecord(i, groupValue, groupCount) {
        if (i < groupCount) {
          CRM.buildCustomData({/literal}"{$contactType}"{literal}, subTypeValues, null, i, groupValue, true).one('crmLoad', function() {
            highlightTabs(this);
            loadNextRecord(i+1, groupValue, groupCount);
          });
        }
      }
      {/literal}
      {foreach from=$customValueCount item="groupCount" key="groupValue"}
      {if $groupValue}{literal}
        loadNextRecord(1, {/literal}{$groupValue}{literal}, {/literal}{$groupCount}{literal});
      {/literal}
      {/if}
      {/foreach}
      {literal}
    }

    loadMultiRecordFields();

    {/literal}{if $oldSubtypes}{literal}
    $('button[name=_qf_Contact_upload_view], button[name=_qf_Contact_upload_new]').click(function() {
      var submittedSubtypes = $('#contact_sub_type').val();
      var oldSubtypes = {/literal}{$oldSubtypes}{literal};

      var warning = false;
      $.each(oldSubtypes, function(index, subtype) {
        if ( $.inArray(subtype, submittedSubtypes) < 0 ) {
          warning = true;
        }
      });
      if ( warning ) {
        return confirm({/literal}'{ts escape="js"}One or more contact subtypes have been de-selected from the list for this contact. Any custom data associated with de-selected subtype will be removed. Click OK to proceed, or Cancel to review your changes before saving.{/ts}'{literal});
      }
      return true;
    });
    {/literal}{/if}{literal}

    // Handle delete of multi-record custom data
    $form.on('click', '.crm-custom-value-del', function(e) {
      e.preventDefault();
      var $el = $(this),
        msg = '{/literal}{ts escape="js"}The record will be deleted immediately. This action cannot be undone.{/ts}{literal}';
      CRM.confirm({title: $el.attr('title'), message: msg})
        .on('crmConfirm:yes', function() {
          var url = CRM.url('civicrm/ajax/customvalue');
          var request = $.post(url, $el.data('post'));
          CRM.status({success: '{/literal}{ts escape="js"}Record Deleted{/ts}{literal}'}, request);
          var addClass = '.add-more-link-' + $el.data('post').groupID;
          $el.closest('div.crm-custom-accordion').remove();
          $('div' + addClass).last().show();
        });
    });

    {/literal}{* Ajax check for matching contacts *}
    {if $checkSimilar == 1}
    var contactType = {$contactType|@json_encode},
      rules = {*$ruleFields|@json_encode*}{literal}[
        'first_name',
        'last_name',
        'nick_name',
        'household_name',
        'organization_name',
        'email'
      ],
      ruleFields = {},
      $ruleElements = $(),
      matchMessage,
      dupeTpl = _.template($('#duplicates-msg-tpl').html()),
      runningCheck = 0;
    $.each(rules, function(i, field) {
      // Match regular fields
      var $el = $('#' + field + ', #' + field + '_1_' + field, $form).filter(':input');
      // Match custom fields
      if (!$el.length && field.lastIndexOf('_') > 0) {
        var pieces = field.split('_');
        field = 'custom_' + pieces[pieces.length-1];
        $el = $('#' + field + ', [name=' + field + '_-1]', $form).filter(':input');
      }
      if ($el.length) {
        ruleFields[field] = $el;
        $ruleElements = $ruleElements.add($el);
      }
    });
    // Check for matches on input when action == ADD
    if (action === 1) {
      $ruleElements.on('change', function () {
        if ($(this).is('input[type=text]') && $(this).val().length < 3) {
          return;
        }
        checkMatches().done(function (data) {
          var params = {
            title: data.count == 1 ? {/literal}"{ts escape='js'}Similar Contact Found{/ts}" : "{ts escape='js'}Similar Contacts Found{/ts}"{literal},
            info: "{/literal}{ts escape='js'}If the contact you were trying to add is listed below, click their name to view or edit their record{/ts}{literal}:",
            contacts: data.values,
            cid: cid
          };
          if (data.count) {
            openDupeAlert(params);
          }
        });
      });
    }

    // Call the api to check for matching contacts
    function checkMatches(rule) {
      var match = {contact_type: contactType},
        response = $.Deferred(),
        checkNum = ++runningCheck,
        params = {
          options: {sort: 'sort_name'},
          return: ['display_name', 'email']
        };
      $.each(ruleFields, function(fieldName, ruleField) {
        if (ruleField.length > 1) {
          match[fieldName] = ruleField.filter(':checked').val();
        } else if (ruleField.is('input[type=text]')) {
          if (ruleField.val().length > 2) {
            match[fieldName] = ruleField.val() + (rule ? '' : '%');
          }
        } else {
          match[fieldName] = ruleField.val();
        }
      });
      // CRM-20565 - Need a good default matching rule before using the dedupe engine for checking on-the-fly.
      // Defaulting to contact.get.
      var action = rule ? 'duplicatecheck' : 'get';
      if (rule) {
        params.rule_type = rule;
        params.match = match;
        params.exclude = cid ? [cid] : [];
      } else {
        _.extend(params, match);
      }
      CRM.api3('contact', action, params).done(function(data) {
        // If a new request has started running, cancel this one.
        if (checkNum < runningCheck) {
          response.reject();
        } else {
          response.resolve(data);
        }
      });
      return response;
    }

    // Open an alert about possible duplicate contacts
    function openDupeAlert(data, iconType) {
      // Close msg if it exists
      matchMessage && matchMessage.close && matchMessage.close();
      matchMessage = CRM.alert(dupeTpl(data), _.escape(data.title), iconType, {expires: false});
      $('.matching-contacts-actions', '#crm-notification-container').on('click', 'a', function() {
        // No confirmation dialog on click
        $('[data-warn-changes=true]').attr('data-warn-changes', 'false');
      });
    }

    // Update the duplicate alert after getting results
    function updateDupeAlert(data, iconType) {
      var $alert = $('.matching-contacts-actions', '#crm-notification-container')
        .closest('.ui-notify-message');
      $alert
        .removeClass('crm-msg-loading success info alert error')
        .addClass(iconType)
        .find('h1').text(data.title);
      $alert
        .find('.notify-content')
        .html(dupeTpl(data));
    }

    // Ajaxify the "Check for Matching Contact(s)" button
    $('#_qf_Contact_refresh_dedupe').click(function(e) {
      var placeholder = {{/literal}
        title: "{ts escape='js'}Fetching Matches{/ts}",
        info: "{ts escape='js'}Checking for similar contacts...{/ts}",
        contacts: []
      {literal}};
      openDupeAlert(placeholder, 'crm-msg-loading');
      checkMatches('Supervised').done(function(data) {
        var params = {
          title: data.count ? {/literal}"{ts escape='js'}Similar Contact Found{/ts}" : "{ts escape='js'}None Found{/ts}"{literal},
          info: data.count ?
            "{/literal}{ts escape='js'}If the contact you were trying to add is listed below, click their name to view or edit their record{/ts}{literal}:" :
            "{/literal}{ts escape='js'}No matches found using the default Supervised deduping rule.{/ts}{literal}",
          contacts: data.values,
          cid: cid
        };
        updateDupeAlert(params, data.count ? 'alert' : 'success');
      });
      e.preventDefault();
    });
    {/literal}{/if}{literal}
  });

  //NYSS 3527 - set comm preferences
  var storeExisting = {};
  function processDeceased( ) {
    if ( cj("#is_deceased").is(':checked') ) {

      //privacy fields
      cj('input[id^=privacy]').each(function(){
        storeExisting[cj(this).prop('id')] = cj(this).prop('checked');
        cj(this).prop('checked', 'checked').prop('onclick', 'return false');
      });

      //opt out
      storeExisting['is_opt_out'] = cj('#is_opt_out').prop('checked')
      cj('#is_opt_out').prop('checked', 'checked').prop('onclick', 'return false');

      //preferred fields
      cj('input[id^=preferred]').each(function(){
        storeExisting[cj(this).prop('id')] = cj(this).prop('checked');
        cj(this).removeAttr('checked').prop('onclick', 'return false');
      });
    }
    else {
      //cycle through stored array when unchecking and restore to previous values
      cj.each(storeExisting, function(id, setting) {
        cj('#' + id).prop('checked', setting).removeAttr('onclick');
      });
    }
  }
  processDeceased();
</script>

<script type="text/template" id="duplicates-msg-tpl">
  <em><%- info %></em>
  <ul class="matching-contacts-actions">
    <% _.forEach(contacts, function(contact) { %>
      <li>
        <a href="<%= CRM.url('civicrm/contact/view', {reset: 1, cid: contact.id}) %>">
          <%- contact.display_name %>
        </a>
        <%- contact.email %>
        <% if (cid) { %>
          <% var params = {reset: 1, action: 'update', oid: contact.id > cid ? contact.id : cid, cid: contact.id > cid ? cid : contact.id }; %>
          (<a href="<%= CRM.url('civicrm/contact/merge', params) %>">{/literal}{ts}Merge{/ts}{literal}</a>)
        <% } %>
      </li>
    <% }); %>
  </ul>
</script>
{/literal}

{* jQuery validate *}
{include file="CRM/Form/validate.tpl"}

{* include common additional blocks tpl *}
{include file="CRM/common/additionalBlocks.tpl"}

{/if}
