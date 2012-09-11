{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* template for building email block*}
<div class="crm-table2div-layout" id="crm-email-content">
  <div class="crm-clear"> <!-- start of main -->
    {if $permission EQ 'edit'}
    {if $email}
     <div class="crm-config-option">
        <a id="edit-email" class="hiddenElement crm-link-action" title="{ts}click to add or edit email addresses{/ts}">
          <span class="batch-edit"></span>{ts}add or edit email{/ts}
      </a>
     </div>
    {else}
      <div>
          <a id="edit-email" class="crm-link-action empty-email" title="{ts}click to add email address{/ts}">
            <span class="batch-edit"></span>{ts}add email{/ts}
        </a>
      </div>
    {/if}
    {/if}
  {foreach from=$email key="blockId" item=item}
    {if $item.email}
      <div class="crm-label">{$item.location_type}&nbsp;{ts}Email{/ts}</div>
      <div class="crm-content crm-contact_email"> <!-- start of content -->
        {*NYSS 4717*}
        <span class={if $privacy.do_not_email}"do-not-email" title="{ts}Privacy flag: Do Not Email{/ts}" {elseif $item.on_hold}"email-hold" title="{ts}Email on hold {if $item.on_hold eq 1}(bounce){elseif $item.on_hold eq 2}(unsubscribe){/if}.{/ts}" {elseif $item.is_primary eq 1}"primary"{/if}>
        {if $privacy.do_not_email || $item.on_hold}{$item.email}
        {else}<a href="mailto:{$item.email}">{$item.email}</a>
        {/if}
        {*NYSS 4603 4601*}
        {if $item.on_hold}&nbsp;({ts}On Hold{/ts}
          {if $item.on_hold == 2} - Opt Out: {/if}
          {if $emailMailing.$blockId.mailingID}
            {assign var=mid value=$emailMailing.$blockId.mailingID}
            <a href="{crmURL p='civicrm/mailing/report/event' q="reset=1&event=bounce&mid=$mid"}" title="{ts}view bounce report{/ts}" target="_blank">{$item.hold_date|crmDate:"%m/%d/%Y"}</a>)
          {else}{$item.hold_date|crmDate:"%m/%d/%Y"})
          {/if}
        {/if}
        {if $item.is_bulkmail}&nbsp;({ts}Bulk{/ts}){/if}
        </span>
        {if $item.signature_text OR $item.signature_html}
        <span class="signature-link description">
            <a href="#" title="{ts}Signature{/ts}" onClick="showHideSignature( '{$blockId}' ); return false;">{ts}(signature){/ts}</a>
        </span>
        {/if}
        <div id="Email_Block_{$blockId}_signature" class="hiddenElement">
          <strong>{ts}Signature HTML{/ts}</strong><br />{$item.signature_html}<br /><br />
        <strong>{ts}Signature Text{/ts}</strong><br />{$item.signature_text|nl2br}</div>
      </div> <!-- end of content -->
    {/if}
  {/foreach}
  </div> <!-- end of main -->
</div>

{literal}
<script type="text/javascript">

{/literal}{if $permission EQ 'edit'}{literal}
cj(function(){
    cj('#email-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-email').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      if ( !cj('#edit-email').hasClass('empty-email') ) { 
          cj('#edit-email').hide();
        }
    });

    cj('#edit-email').click( function() {
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal};

        addCiviOverlay('.crm-summary-email-block');
        cj.ajax({ 
                        data: { 'class_name':'CRM_Contact_Form_Inline_Email' },
                        url: dataUrl,
                        async: false
        }).done( function(response) {
        cj( '#email-block' ).html( response );
    });

        removeCiviOverlay('.crm-summary-email-block');
    });
});
{/literal}{/if}{literal}
function showHideSignature( blockId ) {
  cj("#Email_Block_" + blockId + "_signature").show( );   

  cj("#Email_Block_" + blockId + "_signature").dialog({
      title: "Signature",
      modal: true,
      bgiframe: true,
      width: 900,
      height: 500,
      overlay: { 
          opacity: 0.5, 
          background: "black"
      },

      beforeclose: function(event, ui) {
        cj(this).dialog("destroy");
      },
      open:function() {
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
