{literal}
<script type="text/javascript">
  cj('li.crm-contact-restore').addClass('crm-delete-action');
  cj('li.crm-contact-permanently-delete').addClass('crm-delete-action');
  cj('li.crm-contact-permanently-delete span').html('<div class="icon delete-icon"></div>Delete Contact Permanently');

  //4715 remove delete/trash button; moved to action dropdown
  cj('a.delete.button').parent('li.crm-delete-action.crm-contact-delete').remove();

  //move privacy notes
  var privnote = cj('div#custom-set-content-8 div.crm-content').text();
  var privnotehtml = '<div class="crm-label">Privacy Note</div><div class="crm-content">' + privnote + '</div>';
  cj('.crm-contact-privacy_values').removeClass('upper');
  if ( privnote.length ) {
    cj('.crm-contact-privacy_values').after(privnotehtml);
  }
  cj('div.crm-custom-set-block-8').remove();

  //insert display name
  var displayNameBlock = "<div class='displayName'>{/literal}{$display_name|escape}{literal}</div>";
  cj('div#contactTopBar').before(displayNameBlock);

</script>
{/literal}

{*check delete permanently permission*}
{if !call_user_func(array('CRM_Core_Permission','check'), 'delete contacts permanently') }
{literal}
<script type="text/javascript">
  cj('li.crm-contact-permanently-delete').remove();
</script>
{/literal}
{/if}

{*Assign AddConstInfo custom fields*}
{foreach from=$viewCustomData.1 item=addConstInfo}
  {foreach from=$addConstInfo.fields item=addConstInfoField key=customId}
    {assign var="custom_$customId" value=$addConstInfoField}
  {/foreach}
{/foreach}

{*friend of senator class*}
{if substr_count($custom_19.field_value, 'Yes')}
{literal}
<script type="text/javascript">
  cj('#contact-summary').addClass('friend-of-senator');
</script>
{/literal}
{/if}

{*reconstruct top bar*}
{include file="CRM/Contact/Page/View/nyssSummaryTop.tpl" assign="newTopBar"}
{literal}
<script type="text/javascript">
  cj('#contactTopBar').html('{/literal}{$newTopBar|escape:"javascript"}{literal}').show();
</script>
{/literal}

{*move/reconstruct comm pref*}
<div id="commPrefWrap" style="display:none;">
  <div class="customFieldGroup">
  <table id="communication-preferences">
    <tr class="columnheader">
      <td class="grouplabel">
        <a id="commpref-hdr" class="show-block expanded collapsed" href="#">Communication Preferences</a>
      </td>
    </tr>
    <tr id="commpref-tr">
      <td>
        <div id="comm-pref-block" class="crm-summary-block"></div>
      </td>
    </tr>
  </table>
  </div>
</div>

{literal}
<script type="text/javascript">
  var commPrefWrap = cj('#commPrefWrap').html();
  cj('#customFields .contactCardRight').prepend(commPrefWrap);
  cj('#comm-pref-block').html(cj('.crm-summary-comm-pref-block'));

  cj('a#commpref-hdr').click(function(){
    cj('#commpref-hdr').toggleClass('expanded');
    cj('#commpref-tr').toggle();
    return false;
  });
</script>
{/literal}

{*move/reconstruct contact details (communications)*}
{include file="CRM/Contact/Page/View/nyssContactCommunication.tpl" assign="newCC"}
{literal}
<script type="text/javascript">
  cj('#contact-summary .contact_details').html('{/literal}{$newCC|escape:"javascript"}{literal}');

  //default close district info
  cj('div.crm-address-block .crm-accordion-wrapper').removeClass('crm-accordion-open').addClass
  ('crm-accordion-closed');

  //wrap inline rows in a div
  cj('.crm-summary-block .crm-table2div-layout .crm-label').each(function(){
  cj(this).next('.crm-content').andSelf().wrapAll('<div class="bb-row-wrap"/>');
  });

  //remove Gender from demo block
  cj('#crm-demographic-content .bb-row-wrap').each(function(){
    var labelText2  = cj(this).children('.crm-label').text();
    var removeList2 = ['Gender'];
    if ( cj.inArray(labelText2,removeList2) != -1 ) {
      cj(this).remove();
    }
  });

  //remove custom fields used in top bar
  cj('#custom-set-block-1 .bb-row-wrap').each(function(){
    var labelText1  = cj(this).children('.crm-label').text();
    var content1 = cj(this).children('.crm-content').text();
    var removeList1 = ['Contact Source', 'Individual Category', 'Ethnicity', 'Other Ethnicity', 'Other Gender'];
    if ( cj.inArray(labelText1,removeList1) != -1 ) {
      cj(this).remove();
    }
    //move religion
    if ( labelText1 == 'Religion' ) {
      if ( content1 ) {
        cj('#demographic-block .crm-clear').append(cj(this));
      }
      else {
        cj(this).remove();
      }
    }
  });

  //5638 custom data
  if ( cj('#custom-set-block-1').length ) {
    var custLink1 = cj('#custom-set-block-1 .crm-config-option a').html().replace('add or edit custom set', 'add or edit constituent information');
    cj('#custom-set-block-1 .crm-config-option a').html(custLink1);
  }

  if ( cj('#custom-set-block-3').length ) {
    var custLink3 = cj('#custom-set-block-3 .crm-config-option a').html().replace('add or edit custom set', 'add or edit constituent information');
    cj('#custom-set-block-3 .crm-config-option a').html(custLink3);
  }

  var custLink2 = cj('#custom-set-block-5 .crm-config-option a').html().replace('add or edit custom set', 'add or edit attachments');
  cj('#custom-set-block-5 .crm-config-option a').html(custLink2);

  //5779 truncate file name
  cj('.crm-fileURL a').each(function(){
    var title = cj(this).text();
    if ( title.length > 30 ) {
      var short = cj.trim(title).substring(0, 30).slice(0, -1) + "...";
      cj(this).text(short);
    }
  });

</script>
{/literal}
