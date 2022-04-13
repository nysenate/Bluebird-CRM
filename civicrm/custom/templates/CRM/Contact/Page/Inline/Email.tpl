{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* template for building email block*}
<div id="crm-email-content" {if $permission EQ 'edit'} class="crm-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_Email"{rdelim}' data-dependent-fields='["#crm-contact-actions-wrapper"]'{/if}>
  <div class="crm-clear crm-inline-block-content" {if $permission EQ 'edit'}title="{ts}Add or edit email{/ts}"{/if}>
  {if $permission EQ 'edit'}
    <div class="crm-edit-help">
      <span class="crm-i fa-pencil" aria-hidden="true"></span> {if empty($email)}{ts}Add email{/ts}{else}{ts}Add or edit email{/ts}{/if}
    </div>
  {/if}
  {if empty($email)}
    <div class="crm-summary-row">
      <div class="crm-label">
        {ts}Email{/ts}
        {if $privacy.do_not_email}{privacyFlag field=do_not_email}{/if}
      </div>
      <div class="crm-content"></div>
    </div>
  {/if}
  {foreach from=$email key="blockId" item=item}
    {if $item.email}
    <div class="crm-summary-row {if !empty($item.is_primary)}{ts}primary{/ts}{/if}">
      {*NYSS 4717 4603 4601*}
      <div class="crm-label">
        {$item.location_type} {ts}Email{/ts}
        {if $privacy.do_not_email}<span class="icon privacy-flag do-not-email" title="{ts}Privacy flag: Do Not Email{/ts}"></span>{elseif $item.on_hold}<span class="icon privacy-flag email-hold" title="{ts}Email on hold {if $item.on_hold eq 1}(bounce){elseif $item.on_hold eq 2}(unsubscribe){/if}.{/ts}"></span>{/if}
      </div>
      <div class="crm-content crm-contact_email {if $item.is_primary eq 1}primary{/if}">
        <a href="mailto:{$item.email}">{$item.email}</a>{if $item.on_hold == 2}&nbsp;({ts}On Hold - Opt Out{/ts}){elseif $item.on_hold}&nbsp;({ts}On Hold{/ts}){/if}{if $item.is_bulkmail}&nbsp;({ts}Bulk{/ts}){/if}
          {if $emailMailing.$blockId.mailingID && $item.on_hold}
            {assign var=mid value=$emailMailing.$blockId.mailingID}
            <a href="{crmURL p='civicrm/mailing/report/event' q="reset=1&event=bounce&mid=$mid"}" title="{ts}view bounce report{/ts}" target="_blank">{$item.hold_date|crmDate:"%m/%d/%Y"}</a>
          {elseif $item.on_hold}
            {$item.hold_date|crmDate:"%m/%d/%Y"}
          {/if}
        {if $item.signature_text OR $item.signature_html}
        <span class="signature-link description">
          <a href="#" title="{ts}Signature{/ts}" onClick="showHideSignature( '{$blockId}' ); return false;">{ts}(signature){/ts}</a>
        </span>
        {/if}
        <div id="Email_Block_{$blockId}_signature" class="hiddenElement">
          <strong>{ts}Signature HTML{/ts}</strong><br />{if !empty($item.signature_html)}{$item.signature_html}{/if}<br /><br />
        <strong>{ts}Signature Text{/ts}</strong><br />{if !empty($item.signature_text)}{$item.signature_text|nl2br}{/if}</div>
      </div>
    </div>
    {/if}
  {/foreach}
  </div>
</div>

{literal}
<script type="text/javascript">

function showHideSignature( blockId ) {
  cj("#Email_Block_" + blockId + "_signature").show( );

  cj("#Email_Block_" + blockId + "_signature").dialog({
      title: "Signature",
      modal: true,
      width: 900,
      height: 500,
      beforeclose: function(event, ui) {
        cj(this).dialog("destroy");
      },
      buttons: {
        "Done": function() {
                  cj(this).dialog("destroy");
                }
      }
  });
}
</script>
{/literal}
