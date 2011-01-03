{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
{* this template is used for adding/editing/deleting memberships for a contact  *}
{if $cancelAutoRenew}
<div class="messages status">
    <div class="icon inform-icon"></div>
       <p>{ts 1=$cancelAutoRenew}This membership is set to renew automatically {if $endDate}on {$endDate|crmDate}{/if}. You will need to cancel the auto-renew option if you want to modify the Membership Type, End Date or Membership Status. <a href="%1">Click here</a> if you want to cancel the automatic renewal option.{/ts}</p>
    </div>
{/if}
<div class="spacer"></div>
{if $cdType }
  {include file="CRM/Custom/Form/CustomData.tpl"}
{else}
{if $membershipMode == 'test' }
    {assign var=registerMode value="TEST"}
{elseif $membershipMode == 'live'}
    {assign var=registerMode value="LIVE"}
{/if}
{if !$emailExists and $action neq 8 and $context neq 'standalone'}
<div class="messages status">
    <div class="icon inform-icon"></div>
        <p>{ts}You will not be able to send an automatic email receipt for this Membership because there is no email address recorded for this contact. If you want a receipt to be sent when this Membership is recorded, click Cancel and then click Edit from the Summary tab to add an email address before recording the Membership.{/ts}</p>
</div>
{/if}
{if $context NEQ 'standalone'}
   <h3>{if $action eq 1}{ts}New Membership{/ts}{elseif $action eq 2}{ts}Edit Membership{/ts}{else}{ts}Delete Membership{/ts}{/if}</h3>
{/if}
{if $membershipMode}
    <div id="help">
        {ts 1=$displayName 2=$registerMode}Use this form to submit Membership Record on behalf of %1. <strong>A %2 transaction will be submitted</strong> using the selected payment processor.{/ts}
    </div>
{/if}
<div class="crm-block crm-form-block crm-membership-form-block">
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>       
          {ts}WARNING: Deleting this membership will also delete related membership log and payment records.{/ts} {ts}This action cannot be undone.{/ts} {ts}Consider modifying the membership status instead if you want to maintain a record of this membership.{/ts}
          {ts}Do you want to continue?{/ts}    
      </div>
    {else}
    <table class="form-layout-compressed">
        {if $context neq 'standalone'}
            <tr>
                <td class="font-size12pt label"><strong>{ts}Member{/ts}</strong></td><td class="font-size12pt"><strong>{$displayName}</strong></td>
            </tr>
        {else}
            {include file="CRM/Contact/Form/NewContact.tpl"}
        {/if}
    {if $membershipMode}
	    <tr><td class="label">{$form.payment_processor_id.label}</td><td>{$form.payment_processor_id.html}</td></tr>
	{/if}
 	<tr class="crm-membership-form-block-membership_type_id"><td class="label">{$form.membership_type_id.label}</td><td>{$form.membership_type_id.html}
    {if $member_is_test} {ts}(test){/ts}{/if}<br />
        <span class="description">{ts}Select Membership Organization and then Membership Type.{/ts}</span></td></tr>	
    <tr class="crm-membership-form-block-source"><td class="label">{$form.source.label}</td><td>&nbsp;{$form.source.html}<br />
        <span class="description">{ts}Source of this membership. This value is searchable.{/ts}</span></td></tr>
	<tr class="crm-membership-form-block-join_date"><td class="label">{$form.join_date.label}</td><td>{include file="CRM/common/jcalendar.tpl" elementName=join_date}
		<br />
        <span class="description">{ts}When did this contact first become a member?{/ts}</span></td></tr>
 	<tr class="crm-membership-form-block-start_date"><td class="label">{$form.start_date.label}</td><td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}
		<br />
        <span class="description">{ts}First day of current continuous membership period. Start Date will be automatically set based on Membership Type if you don't select a date.{/ts}</span></td></tr>
 	<tr class="crm-membership-form-block-end_date"><td class="label">{$form.end_date.label}</td>
	<td>{if $isRecur && $endDate}{$endDate|crmDate}{else}{include file="CRM/common/jcalendar.tpl" elementName=end_date}{/if}
		<br />
        <span class="description">{ts}Latest membership period expiration date. End Date will be automatically set based on Membership Type if you don't select a date.{/ts}</span></td></tr>
        <tr id="autorenew" class="crm-membership-form-block-auto_renew">
           <td class="label"> {$form.auto_renew.label} </td>
           <td> {$form.auto_renew.html} </td>
        </tr>
    {if ! $membershipMode}
        <tr><td class="label">{$form.is_override.label}</td><td>{$form.is_override.html}&nbsp;&nbsp;{help id="id-status-override"}</td></tr>
    {/if}

    {if ! $membershipMode}
    {* Show read-only Status block - when action is UPDATE and is_override is FALSE *}
        {if $action eq 2}
        <tr id="memberStatus_show">
            <td class="label">{$form.status_id.label}</td><td class="view-value">{$membershipStatus}</td>
        </tr>
	<tr class="crm-membership-form-block-auto_renew">
           <td class="label"> {$form.auto_renew.label} </td>
           <td> {$form.auto_renew.html} </td>
        </tr>
        {else}{* Show editable status field when is_override is TRUE *}
        <tr id="memberStatus"><td class="label">{$form.status_id.label}</td><td>{$form.status_id.html}<br />
            <span class="description">{ts}If <strong>Status Override</strong> is checked, the selected status will remain in force (it will NOT be modified by the automated status update script).{/ts}</span></td></tr>
        {/if}
    {elseif $membershipMode}
        <tr class="crm-membership-form-block-billing"><td colspan="2">
        {include file='CRM/Core/BillingBlock.tpl'}
        </td></tr>
    {/if}
        {if $accessContribution and ! $membershipMode AND ($action neq 2 or !$rows.0.contribution_id or $onlinePendingContributionId)}
        <tr id="contri">
            <td class="label">{if $onlinePendingContributionId}{ts}Update Payment Status{/ts}{else}{$form.record_contribution.label}{/if}</td>
            <td>{$form.record_contribution.html}<br />
                <span class="description">{ts}Check this box to enter or update payment information. You will also be able to generate a customized receipt.{/ts}</span></td>
            </tr>
        <tr class="crm-membership-form-block-record_contribution"><td colspan="2">    
          <fieldset id="recordContribution"><legend>{ts}Membership Payment and Receipt{/ts}</legend>
              <table>
                  <tr class="crm-membership-form-block-contribution_type_id">
                      <td class="label">{$form.contribution_type_id.label}</td>
                      <td>{$form.contribution_type_id.html}<br />
                      <span class="description">{ts}Select the appropriate contribution type for this payment.{/ts}</span></td>
                  </tr>
                  <tr class="crm-membership-form-block-total_amount">
                      <td class="label">{$form.total_amount.label}</td>
                      <td>{$form.total_amount.html}<br />
                	  <span class="description">{ts}Membership payment amount. A contribution record will be created for this amount.{/ts}</span></td>
                  </tr>
                  <tr class="crm-membership-form-block-receive_date">
                      <td class="label">{$form.receive_date.label}</td>
                      <td>{include file="CRM/common/jcalendar.tpl" elementName=receive_date}</td>  
                  </tr>
                  <tr class="crm-membership-form-block-payment_instrument_id">
                      <td class="label">{$form.payment_instrument_id.label}</td>
                      <td>{$form.payment_instrument_id.html}</td>
                  </tr>
		          <tr id="checkNumber" class="crm-membership-form-block-check_number">
                      <td class="label">{$form.check_number.label}</td>
                      <td>{$form.check_number.html|crmReplace:class:six}</td>
                  </tr>
	   	       {if $action neq 2 }	
                  <tr class="crm-membership-form-block-trxn_id">
	    	          <td class="label">{$form.trxn_id.label}</td>
                      <td>{$form.trxn_id.html}</td>
                  </tr>
	   	       {/if}
                  <tr class="crm-membership-form-block-contribution_status_id">		
		              <td class="label">{$form.contribution_status_id.label}</td>
                      <td>{$form.contribution_status_id.html}</td>
                  </tr>
              </table>
          </fieldset>
        </td></tr>
    {else}
        <div class="spacer"></div>
	{/if}

    {if $emailExists and $outBound_option != 2 }
        <tr id="send-receipt" class="crm-membership-form-block-send_receipt">
            <td class="label">{$form.send_receipt.label}</td><td>{$form.send_receipt.html}<br />
            <span class="description">{ts 1=$emailExists}Automatically email a membership confirmation and receipt to %1?{/ts}</span></td>
        </tr>
    {elseif $context eq 'standalone' and $outBound_option != 2 }
        <tr id="email-receipt" style="display:none;">
            <td class="label">{$form.send_receipt.label}</td><td>{$form.send_receipt.html}<br />
            <span class="description">{ts}Automatically email a membership confirmation and receipt to {/ts}<span id="email-address"></span>?</span></td>
        </tr>
    {/if}    
        <tr id='notice' style="display:none;">
            <td class="label">{$form.receipt_text_signup.label}</td>
            <td class="html-adjust"><span class="description">{ts}If you need to include a special message for this member, enter it here. Otherwise, the confirmation email will include the standard receipt message configured under System Message Templates.{/ts}</span>
                 {$form.receipt_text_signup.html|crmReplace:class:huge}</td>
        </tr>
    </table>
    <div id="customData"></div>
    {*include custom data js file*}
    {include file="CRM/common/customData.tpl"}
	{literal}
		<script type="text/javascript">
			cj(document).ready(function() {
				{/literal}
				buildCustomData( '{$customDataType}' );
				{if $customDataSubType}
					buildCustomData( '{$customDataType}', {$customDataSubType} );
				{/if}
				{literal}
			});
		</script>
	{/literal}
	{if $accessContribution and $action eq 2 and $rows.0.contribution_id}
        <fieldset>	 
            {include file="CRM/Contribute/Form/Selector.tpl" context="Search"}
        </fieldset>
	{/if}
   {/if}
    
    <div class="spacer"></div>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div> <!-- end form-block -->

{if $action neq 8} {* Jscript additions not need for Delete action *} 
{if $accessContribution and !$membershipMode AND ($action neq 2 or !$rows.0.contribution_id or $onlinePendingContributionId)}

{literal}
<script type="text/javascript">
cj( function( ) {
    cj('#record_contribution').click( function( ) {
        if ( cj(this).attr('checked') ) {
            cj('#recordContribution').show( );
            setPaymentBlock( );
        } else {
            cj('#recordContribution').hide( );
        }
    });
    
    cj('#membership_type_id\\[1\\]').change( function( ) {
        setPaymentBlock( );
    });
});
</script>
{/literal}

{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="record_contribution"
    trigger_value       =""
    target_element_id   ="recordContribution" 
    target_element_type ="table-row"
    field_type          ="radio"
    invert              = 0
}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="payment_instrument_id"
    trigger_value       = '4'
    target_element_id   ="checkNumber" 
    target_element_type ="table-row"
    field_type          ="select"
    invert              = 0
}
{/if}
{if ($emailExists and $outBound_option != 2) OR $context eq 'standalone' }
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="send_receipt"
    trigger_value       =""
    target_element_id   ="notice" 
    target_element_type ="table-row"
    field_type          ="radio"
    invert              = 0
}
{/if}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="auto_renew"
    trigger_value       =""
    target_element_id   ="send-receipt" 
    target_element_type ="table-row"
    field_type          ="radio"
    invert              = 1
}
{literal}
<script type="text/javascript">
{/literal}
{if !$membershipMode && !$isRecur}
{literal}
showHideMemberStatus();

function showHideMemberStatus() {
	if (document.getElementsByName("is_override")[0].checked == true) {
	   cj('#memberStatus').show( );
       cj('#memberStatus_show').hide( );
	} else {
	   cj('#memberStatus').hide( );
       cj('#memberStatus_show').show( );
	}
}
{/literal}
{/if}
{literal}
function setPaymentBlock( ) {
    var memType = cj('#membership_type_id\\[1\\]').val( );
    
    if ( !memType ) {
        return;
    }
    
    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/memType' h=0}"{literal};
    
    cj.post( dataUrl, {mtype: memType}, function( data ) {
        cj("#contribution_type_id").val( data.contribution_type_id );
        cj("#total_amount").val( data.total_amount );
    }, 'json');    
}

{/literal}
{if $context eq 'standalone' and $outBound_option != 2 }
{literal}
cj( function( ) {
    cj("#contact").blur( function( ) {
        checkEmail( );
    } );
    checkEmail( );
});
function checkEmail( ) {
    var contactID = cj("input[name=contact_select_id]").val();
    if ( contactID ) {
        var postUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' h=0}{literal}";
        cj.post( postUrl, {contact_id: contactID},
            function ( response ) {
                if ( response ) {
                    cj("#email-receipt").show( );
                    if ( cj("#send_receipt").is(':checked') ) {
                        cj("#notice").show( );
                    }
                
                    cj("#email-address").html( response );
                } else {
                    cj("#email-receipt").hide( );
                    cj("#notice").hide( );
                }
            }
        );
    }
}
{/literal}
{/if}
{if $autoRenew}
{literal}
   var renew = {/literal}{$autoRenew}{literal};
   
   //keep read only always checked.
   cj( function( ) {
      var allowAutoRenew = {/literal}'{$allowAutoRenew}'{literal};
      if ( allowAutoRenew ) {
          cj( "#auto_renew" ).click(function( ) {
              if ( cj(this).attr( 'readonly' ) ) { 
                 cj(this).attr( 'checked', true );
              }
          });
       }
    }); 
{/literal}
{/if}

{literal}
cj("#autorenew").hide( );

function checkProcessor( Id ) {
  var recur = {/literal}{$recurProcessor}{literal};	 

  if( recur && !recur[Id] ) {
    cj("#autorenew").hide( );
    return false;
  } else {
    memType = cj('#membership_type_id\\[1\\]').val( );
    buildAutoRenew( memType );
  }
}

function buildAutoRenew( memType ) {
  var recur = {/literal}{$recurProcessor}{literal};
  var processor = cj("#payment_processor_id").val();
  if ( !recur[processor] ) {
      cj("#autorenew").hide( );
      return false;
  }
  if ( renew ) {
     if ( renew[memType] == 1 || renew[memType] == 2 ) {
        cj("#autorenew").show( );
        if ( renew[memType] == 2 ) {
           cj("#auto_renew").attr( 'checked', true );
           cj("#auto_renew").attr( 'readonly', true );
        } else {
           cj("#auto_renew").attr( 'checked', false );
           cj("#auto_renew").removeAttr( 'readonly' );
        }
     } else {
        cj("#autorenew").hide( );
	cj("#auto_renew").attr( 'checked', false );
        cj("#send-receipt").show( );
        return;
     } 
  }

  if ( cj("#auto_renew").attr( 'checked' ) ) {
      cj("#send_receipt").attr( 'checked', false );
      cj("#send-receipt").hide( );
      cj("#notice").hide( );
  } else {
      cj("#send-receipt").show( );
  }
}

function showHideNotice( )
{
   if ( cj("#auto_renew").attr( 'checked' ) ) {
       cj("#notice").hide( );
   } else if ( cj("#send_receipt").attr( 'checked' ) ) {
       cj("#notice").show( );
   }
}
</script>
{/literal}
{/if} {* closing of delete check if *} 
{/if}{* closing of custom data if *}
