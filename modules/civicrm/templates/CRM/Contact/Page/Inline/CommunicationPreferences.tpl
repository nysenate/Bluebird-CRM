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
{* template for building communication preference block*}
<div class="crm-table2div-layout" id="crm-communication-pref-content">
  <div class="crm-clear"><!-- start of main -->
    {if $permission EQ 'edit'}
      <div class="crm-config-option">
        <a id="edit-communication-pref" class="hiddenElement crm-link-action" title="{ts}click to add or edit communication preferences{/ts}">
        <span class="batch-edit"></span>{ts}add or edit communication preferences{/ts}
        </a>
      </div>
    {/if}
    <div class="crm-label">{ts}Privacy{/ts}</div>
    <div class="crm-content crm-contact-privacy_values font-red upper">
      {foreach from=$privacy item=priv key=index}
        {if $priv}{$privacy_values.$index}<br/>{/if}
      {/foreach}
      {if $is_opt_out}{ts}No Bulk Emails (User Opt Out){/ts}{/if}
    </div>
    <div class="crm-label">{ts}Preferred Method(s){/ts}</div>
    <div class="crm-content crm-contact-preferred_communication_method_display">
      {$preferred_communication_method_display}
    </div>
    {if $preferred_language}
    <div class="crm-label">{ts}Preferred Language{/ts}</div>
    <div class="crm-content crm-contact-preferred_language">
      {$preferred_language}
    </div>
    {/if}
    <div class="crm-label">{ts}Email Format{/ts}</div>
    <div class="crm-content crm-contact-preferred_mail_format">
      {$preferred_mail_format}
    </div>
    <div class="crm-label">{ts}Email Greeting{/ts}</div>
    <div class="crm-content crm-contact-email_greeting_display">
      {$email_greeting_display}
      {if !empty($email_greeting_custom)}<span class="crm-custom-greeting">({ts}Customized{/ts})</span>{/if}
    </div>
    <div class="crm-label">{ts}Postal Greeting{/ts}</div>
    <div class="crm-content crm-contact-postal_greeting_display">
      {$postal_greeting_display}
      {if !empty($postal_greeting_custom)}<span class="crm-custom-greeting" >({ts}Customized{/ts})</span>{/if}
    </div>
    <div class="crm-label">{ts}Addressee{/ts}</div>
    <div class="crm-content crm-contact-addressee_display">
      {$addressee_display}
      {if !empty($addressee_custom)}<span class="crm-custom-greeting">({ts}Customized{/ts})</span>{/if}
    </div>
  </div> <!-- end of main -->
</div> <!-- end of table layout -->
 
{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#communication-pref-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-communication-pref').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      cj('#edit-communication-pref').hide();
    });

    // update email and phone block based on privacy settings
    var doNotEmail = {/literal}{$do_not_email}{literal};
    
    if (doNotEmail) {
      cj('.crm-contact_email span').addClass('do-not-email');
    }
    else {
      cj('.crm-contact_email span').removeClass('do-not-email');
    }

    var doNotPhone = {/literal}{$do_not_phone}{literal};
    
    if (doNotPhone) {
      cj('.crm-contact_phone span').addClass('do-not-phone');
    }
    else {
      cj('.crm-contact_phone span').removeClass('do-not-phone');
    }

    cj('#edit-communication-pref').click( function() {
      var dataUrl  = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
      
      addCiviOverlay('.crm-summary-comm-pref-block');
      cj.ajax({
        data: { 'class_name':'CRM_Contact_Form_Inline_CommunicationPreferences' },
        url: dataUrl,
        async: false
      }).done( function(response) {
        cj( '#communication-pref-block' ).html( response );
      });

      removeCiviOverlay('.crm-summary-comm-pref-block');
    });
});
</script>
{/literal}
{/if}
