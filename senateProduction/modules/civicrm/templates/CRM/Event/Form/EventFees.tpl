{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
{if $paid} {* We retrieve this tpl when event is selected - keep it empty if event is not paid *} 
<fieldset id="priceset">
    <table class="form-layout">
    {if $priceSet}
    	{if $action eq 2} {* Updating *}
            {if $lineItem}	
                <tr class="crm-event-eventfees-form-block-line_items">
                    <td class="label">{ts}Event Fees{/ts}</td>
                    <td>{include file="CRM/Price/Page/LineItem.tpl" context="Event"}</td>
                </tr>
            {else}
                <tr class="crm-event-eventfees-form-block-event_level">
                    <td class="label">{ts}Event Level{/ts}</td>
                    <td>{$fee_level}&nbsp;{if $fee_amount}- {$fee_amount|crmMoney:$fee_currency}{/if}</td>
                </tr>
            {/if}
        {else} {* New participant *}
        <tr class="crm-event-eventfees-form-block-price_set_amount">  
        <td class="label" style="padding-top: 10px;">{$form.amount.label}</td>
        <td class="view-value"><table class="form-layout-compressed">
         {if $priceSet.help_pre AND $action eq 1}
            <tr class="crm-event-eventfees-form-block-price_set_help_pre"><td colspan=2><span class="description">{$priceSet.help_pre}</span></td></tr>
         {/if}
        {foreach from=$priceSet.fields item=element key=field_id}
         {if ($element.html_type eq 'CheckBox' || $element.html_type == 'Radio') && $element.options_per_line}
            {assign var="element_name" value=price_$field_id}
            {assign var="count" value="1"}
            <tr class="crm-event-eventfees-form-block-price_set_elements"><td class="label">{$form.$element_name.label}</td>
                <td class="view-value">
                <table class="form-layout-compressed">
                <tr class="crm-event-eventfees-form-block-price_set_element">	
            {foreach name=outer key=key item=item from=$form.$element_name}
                    {if is_numeric($key) }
                        <td class="labels font-light"><td>{$form.$element_name.$key.html}</td>
                        {if $count == $element.options_per_line}
                            {assign var="count" value="1"}
                            </tr>
                            <tr>			
                        {else}
                            {assign var="count" value=`$count+1`}
                        {/if}
                    {/if}
                {/foreach}
                </tr>
                {if $element.help_post AND $action eq 1}
                    <tr class="crm-event-eventfees-form-block-price_set_element_help_post"><td></td><td><span class="description">{$element.help_post}</span></td></tr>
                {/if}
                </table>
              </td>
            </tr>
          {else}	
            {assign var="name" value=`$element.name`}
            {assign var="element_name" value="price_"|cat:$field_id}
            <tr class="crm-event-eventfees-form-block-price_set_element_help_post"><td class="label"> {$form.$element_name.label}</td>
                <td class="view-value">{$form.$element_name.html}
                    {if $element.help_post AND $action eq 1}
                        <br /><span class="description">{$element.help_post}</span>
                    {/if}
               </td>
            </tr>
          {/if}
       {/foreach}
         {if $priceSet.help_post AND $action eq 1}
            <tr class="crm-event-eventfees-form-block-price_set_help_post"><td colspan=2><span class="description">{$priceSet.help_post}</span></td></tr>
         {/if}
      </table>
    </td>
</tr>
<tr class="crm-event-eventfees-form-block-price_set_calculate"><td></td>
    <td align="left">
      {include file="CRM/Price/Form/Calculate.tpl"} 
    </td>
</tr>
    {/if}	
    {else} {* NOT Price Set *}
     <tr>
     <td class ='html-adjust' colspan=2>
     	<table class="form-layout" style="width: auto;">
        {if $discount}
            <tr class="crm-event-eventfees-form-block-discount"><td class="label">&nbsp;&nbsp;{ts}Discount Set{/ts}</td><td class="view-value">{$discount}</td></tr>
        {elseif $form.discount_id.label}
            <tr class="crm-event-eventfees-form-block-discount_id"><td class="label">&nbsp;&nbsp;{$form.discount_id.label}</td><td>{$form.discount_id.html}</td></tr>
        {/if}
        {if $action EQ 2}
            <tr class="crm-event-eventfees-form-block-fee_level"><td class="label">&nbsp;&nbsp;{ts}Event Level{/ts}</td><td class="view-value"><span class="bold">{$fee_level}&nbsp;{if $fee_amount}- {$fee_amount|crmMoney:$fee_currency}{/if}</span></td></tr>
        {else}
            <tr class="crm-event-eventfees-form-block-fee_amount"><td class="label">&nbsp;&nbsp;{$form.amount.label}</td><td>{$form.amount.html}
        {/if}
        {if $action EQ 1}
            <br />&nbsp;&nbsp;<span class="description">{ts}Event Fee Level (if applicable).{/ts}</span>
        {/if}
        </td></tr>
     	</table>
     </td>
     </tr>
    {/if}

    {if $accessContribution and ! $participantMode and ($action neq 2 or !$rows.0.contribution_id or $onlinePendingContributionId) }
        <tr class="crm-event-eventfees-form-block-record_contribution">
            <td class="label">{$form.record_contribution.label}</td>
            <td>{$form.record_contribution.html}<br />
                <span class="description">{ts}Check this box to enter payment information. You will also be able to generate a customized receipt.{/ts}</span>
            </td>
        </tr>
        <tr id="payment_information" class="crm-event-eventfees-form-block-payment_information">
           <td class ='html-adjust' colspan=2>
           <fieldset><legend>{ts}Payment Information{/ts}</legend>
             <table id="recordContribution" class="form-layout" style="width:auto;">
                <tr class="crm-event-eventfees-form-block-contribution_type_id">
                    <td class="label">{$form.contribution_type_id.label}<span class="marker"> *</span></td>
                    <td>{$form.contribution_type_id.html}<br /><span class="description">{ts}Select the appropriate contribution type for this payment.{/ts}</span></td>
                </tr>
                <tr class="crm-event-eventfees-form-block-total_amount"><td class="label">{$form.total_amount.label}</td><td>{$form.total_amount.html|crmMoney:$currency}<br/><span class="description">{ts}Actual payment amount for this registration.{/ts}</span></td></tr>
                <tr>
                    <td class="label" >{$form.receive_date.label}</td>
                    <td>{include file="CRM/common/jcalendar.tpl" elementName=receive_date}</td>
                </tr> 
                <tr class="crm-event-eventfees-form-block-payment_instrument_id"><td class="label">{$form.payment_instrument_id.label}</td><td>{$form.payment_instrument_id.html}</td></tr>
                <tr id="checkNumber" class="crm-event-eventfees-form-block-check_number"><td class="label">{$form.check_number.label}</td><td>{$form.check_number.html|crmReplace:class:six}</td></tr>
                {if $showTransactionId }	
                    <tr class="crm-event-eventfees-form-block-trxn_id"><td class="label">{$form.trxn_id.label}</td><td>{$form.trxn_id.html}</td></tr>	
                {/if}	
                <tr class="crm-event-eventfees-form-block-contribution_status_id"><td class="label">{$form.contribution_status_id.label}</td><td>{$form.contribution_status_id.html}</td></tr>      
             </table>
           </fieldset>
           </td>
        </tr>

        {* Record contribution field only present if we are NOT in submit credit card mode (! participantMode). *}
        {include file="CRM/common/showHideByFieldValue.tpl" 
            trigger_field_id    ="record_contribution"
            trigger_value       =""
            target_element_id   ="payment_information" 
            target_element_type ="table-row"
            field_type          ="radio"
            invert              = 0
        }
    {/if}
    </table>
</fieldset>
{/if}

{* credit card block when it is live or test mode*}
{if $participantMode and $paid}	
  <div class="spacer"></div>
  {include file='CRM/Core/BillingBlock.tpl'}
{/if}
{if ($email OR $batchEmail) and $outBound_option != 2}
    <fieldset id="send_confirmation_receipt"><legend>{if $paid}{ts}Registration Confirmation and Receipt{/ts}{else}{ts}Registration Confirmation{/ts}{/if}</legend>  
      <table class="form-layout" style="width:auto;">
		 <tr class="crm-event-eventfees-form-block-send_receipt"> 
            <td class="label">{if $paid}{ts}Send Confirmation and Receipt{/ts}{else}{ts}Send Confirmation{/ts}{/if}</td>
            <td>{$form.send_receipt.html}<br>
              {if $paid}
                <span class="description">{ts 1=$email}Automatically email a confirmation and receipt to %1?{/ts}</span></td>
              {else}
                <span class="description">{ts 1=$email}Automatically email a confirmation to %1?{/ts}</span></td>
              {/if}
        </tr>
        <tr id='notice' class="crm-event-eventfees-form-block-receipt_text">
 			<td class="label">{$form.receipt_text.label}</td> 
            <td><span class="description">
                {ts}Enter a message you want included at the beginning of the confirmation email. EXAMPLE: 'Thanks for registering for this event.'{/ts}
                </span><br />
                {$form.receipt_text.html|crmReplace:class:huge}
            </td>
        </tr> 
      </table>
    </fieldset>
{elseif $context eq 'standalone' and $outBound_option != 2 }
    <fieldset id="email-receipt" style="display:none;"><legend>{if $paid}{ts}Registration Confirmation and Receipt{/ts}{else}{ts}Registration Confirmation{/ts}{/if}</legend>  
      <table class="form-layout" style="width:auto;">
    	 <tr class="crm-event-eventfees-form-block-send_receipt"> 
            <td class="label">{if $paid}{ts}Send Confirmation and Receipt{/ts}{else}{ts}Send Confirmation{/ts}{/if}</td>
            <td>{$form.send_receipt.html}<br>
              {if $paid}
                <span class="description">{ts 1='<span id="email-address"></span>'}Automatically email a confirmation and receipt to %1?{/ts}</span>
              {else}
                <span class="description">{ts 1='<span id="email-address"></span>'}Automatically email a confirmation to %1?{/ts}</span>
              {/if}
            </td>
        </tr>
        <tr id='notice' class="crm-event-eventfees-form-block-receipt_text">
    		<td class="label">{$form.receipt_text.label}</td> 
            <td><span class="description">
                {ts}Enter a message you want included at the beginning of the confirmation email. EXAMPLE: 'Thanks for registering for this event.'{/ts}
                </span><br />
                {$form.receipt_text.html|crmReplace:class:huge}</td>
        </tr>
      </table>
    </fieldset>
{/if}

{if ($email and $outBound_option != 2) OR $context eq 'standalone' } {* Send receipt field only present if contact has a valid email address. *}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="send_receipt"
    trigger_value       =""
    target_element_id   ="notice" 
    target_element_type ="table-row"
    field_type          ="radio"
    invert              = 0
}
{/if}

{if $action eq 1 and !$participantMode} 
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="payment_instrument_id"
    trigger_value       = '4'
    target_element_id   ="checkNumber" 
    target_element_type ="table-row"
    field_type          ="select"
    invert              = 0
}
{/if} 

{if $context eq 'standalone' and $outBound_option != 2 }
<script type="text/javascript">
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
</script>
{/if}

{if $onlinePendingContributionId}
<script type="text/javascript">
{literal}
  function confirmStatus( pStatusId, cStatusId ) {
     if ( (pStatusId == cj("#status_id").val() ) && (cStatusId == cj("#contribution_status_id").val()) ) {
         var allow = confirm( '{/literal}{ts}The Payment Status for this participant is Completed. The Participant Status is set to Pending from pay later. Click Cancel if you want to review or modify these values before saving this record{/ts}{literal}.' );       
         if ( !allow ) return false; 
     }
  }

  function checkCancelled( statusId, pStatusId, cStatusId ) {
    //selected participant status is 'cancelled'
    if ( statusId == pStatusId ) {
       cj("#contribution_status_id").val( cStatusId );
       
       //unset value for send receipt check box.
       cj("#send_receipt").attr( "checked", false );
       cj("#send_confirmation_receipt").hide( );

       // set receive data to null.
       document.getElementById("receive_date[M]").value = null;
       document.getElementById("receive_date[d]").value = null;
       document.getElementById("receive_date[Y]").value = null;
    } else {
       cj("#send_confirmation_receipt").show( );
    }	
    sendNotification();
  }

{/literal}
</script>
{/if}
{if $showFeeBlock && !$priceSet && $action neq 2}
<script>
{literal}
     fillTotalAmount( );
     
     function fillTotalAmount( totalAmount ) {
          if ( !totalAmount ) {
     	      var eventFeeBlockValues = {/literal}{$eventFeeBlockValues}{literal};
	      totalAmount = eval('eventFeeBlockValues.amount_id_'+{/literal}{$form.amount.value}{literal});
	  }
          cj('#total_amount').val( totalAmount );
     }
{/literal}
</script>
{/if}

{* ADD mode if *}    
