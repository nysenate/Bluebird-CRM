{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
{* This template is used for adding/editing/deleting offline Event Registrations *}
{if $showFeeBlock }
    {include file="CRM/Event/Form/EventFees.tpl"}
{elseif $cdType }
    {include file="CRM/Custom/Form/CustomData.tpl"}
{else}
    {if $participantMode == 'test' }
        {assign var=registerMode value="TEST"}
        {else if $participantMode == 'live'}
        {assign var=registerMode value="LIVE"}
    {/if}
    <h3>{if $action eq 1}{ts}New Event Registration{/ts}{elseif $action eq 8}{ts}Delete Event Registration{/ts}{else}{ts}Edit Event Registration{/ts}{/if}</h3>
    <div class="crm-block crm-form-block crm-participant-form-block">
    <div class="view-content">
    {if $participantMode}
        <div id="help">
        	{ts 1=$displayName 2=$registerMode}Use this form to submit an event registration on behalf of %1. <strong>A %2 transaction will be submitted</strong> using the selected payment processor.{/ts}
        </div>
    {/if}
    <div id="eventFullMsg" class="messages status" style="display:none;"></div>
  

    {if $action eq 1 AND $paid}
    	<div id="help">
    		{ts}If you are accepting offline payment from this participant, check <strong>Record Payment</strong>. You will be able to fill in the payment information, and optionally send a receipt.{/ts}
    	</div>  
    {/if}

        {if $action eq 8} {* If action is Delete *}
    		<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
		<div class="crm-participant-form-block-delete messages status">
                <div class="crm-content">
                    <div class="icon inform-icon"></div> &nbsp;
                    {ts}WARNING: Deleting this registration will result in the loss of related payment records (if any).{/ts} {ts}Do you want to continue?{/ts}
                </div>
    			{if $additionalParticipant}
                    <div class="crm-content">
                        {ts 1=$additionalParticipant} There are %1 more Participant(s) registered by this participant. Deleting this registration will also result in deletion of these additional participant(s).{/ts}
                    </div>
    			{/if}
            </div>
        {else} {* If action is other than Delete *}
            <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
            <table class="form-layout-compressed">
            {if $single and $context neq 'standalone'}
    			<tr class="crm-participant-form-block-displayName">
    			    <td class="label font-size12pt"><label>{ts}Participant{/ts}</label></td>
    			    <td class="font-size12pt view-value">{$displayName}&nbsp;</td>
    			</tr>
    	    {else}
                {include file="CRM/Contact/Form/NewContact.tpl"}
            {/if}	
            {if $participantMode}
                <tr class="crm-participant-form-block-payment_processor_id"><td class="label nowrap">{$form.payment_processor_id.label}</td><td>{$form.payment_processor_id.html}</td></tr>
            {/if}
            <tr class="crm-participant-form-block-event_id">
                <td class="label">{$form.event_id.label}</td><td class="view-value bold">{$form.event_id.html}&nbsp;        
    				{if $action eq 1 && !$past }<br /><a href='javascript:buildSelect( "event_id" );' id='past-event'>&raquo; {ts}Include past event(s) in this select list.{/ts}</a>{/if}    
    				{if $is_test}
    					{ts}(test){/ts}
    				{/if}
                </td>
            </tr> 
            <tr class="crm-participant-form-block-role_id"><td class="label">{$form.role_id.label}</td><td>{$form.role_id.html}</td></tr>
            <tr class="crm-participant-form-block-register_date">
                <td class="label">{$form.register_date.label}</td>
                <td>
                    {if $hideCalendar neq true}
                        {include file="CRM/common/jcalendar.tpl" elementName=register_date}
                    {else}
                        {$form.register_date.html|crmDate}
                    {/if}
    			</td>
    		</tr>
    		<tr class="crm-participant-form-block-status_id">
    			<td class="label">{$form.status_id.label}</td>
			<td>{$form.status_id.html}{if $event_is_test} {ts}(test){/ts}{/if}
			        <div id="notify">{$form.is_notify.html}{$form.is_notify.label}</div>
			</td>
    		</tr>
    		<tr class="crm-participant-form-block-source">
    		    <td class="label">{$form.source.label}</td><td>{$form.source.html|crmReplace:class:huge}<br />
                <span class="description">{ts}Source for this registration (if applicable).{/ts}</span></td>
            </tr>
            </table>

            {* Fee block (EventFees.tpl) is injected here when an event is selected. *}
            <div id="feeBlock"></div>

            <fieldset>
            <table class="form-layout">
                <tr class="crm-participant-form-block-note">
                    <td class="label">{$form.note.label}</td><td>{$form.note.html}</td>
                </tr>
            </table>
            </fieldset>

            <div class="crm-participant-form-block-customData">
                <div id="customData" class="crm-customData-block"></div>  {* Participant Custom data *}
                <div id="customData{$eventNameCustomDataTypeID}" class="crm-customData-block"></div> {* Event Custom Data *}
                <div id="customData{$roleCustomDataTypeID}" class="crm-customData-block"></div> {* Role Custom Data *}	
                <div id="customData{$eventTypeCustomDataTypeID}" class="crm-customData-block"></div> {* Role Custom Data *}	
            </div>
    	{/if}
		 
        {if $accessContribution and $action eq 2 and $rows.0.contribution_id}
            {include file="CRM/Contribute/Form/Selector.tpl" context="Search"}
        {/if}

        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
</div>
{if $action eq 1 or $action eq 2}
{literal}
<script type="text/javascript">
    // event select
    function buildSelect( selectID ) {
        var elementID = '#' + selectID;
        cj( elementID ).html('');
        var postUrl = "{/literal}{crmURL p='civicrm/ajax/eventlist' h=0}{literal}";
        cj.post( postUrl, null,
            function ( response ) {
                response = eval( response );
                for (i = 0; i < response.length; i++) {
                    cj( elementID ).get(0).add(new Option(response[i].name, response[i].value), document.all ? i : null);
                }
                cj('#past-event').hide( );
                cj('input[name=past_event]').val(1);
                cj("#feeBlock").html( '' );
            }
        );
    }
    {/literal}

    {if $preloadJSSnippet}
       {$preloadJSSnippet}
    {else}
      //build fee block
      buildFeeBlock( );
   {/if}

   {literal}	
    //build discount block
    if ( document.getElementById('discount_id') ) {
      var discountId  = document.getElementById('discount_id').value;
      if ( discountId ) {
	var eventId  = document.getElementById('event_id').value;
	buildFeeBlock( eventId, discountId );    
      }
    }

	function buildFeeBlock( eventId, discountId )
	{
		var dataUrl = {/literal}"{crmURL p=$urlPath h=0 q='snippet=4'}";
                dataUrl = dataUrl + '&qfKey=' + '{$qfKey}'
 
		{if $urlPathVar}
		dataUrl = dataUrl + '&' + '{$urlPathVar}'
		{/if}

		{literal}

		if ( !eventId ) {
			var eventId  = document.getElementById('event_id').value;
		}

		if ( eventId) {
			dataUrl = dataUrl + '&eventId=' + eventId;	
		} else {
                        cj('#eventFullMsg').hide( );
			cj('#feeBlock').html('');
			return;
		}

		var participantId  = "{/literal}{$participantId}{literal}";

		if ( participantId ) {
			dataUrl = dataUrl + '&participantId=' + participantId;	
		}

		if ( discountId ) {
			dataUrl = dataUrl + '&discountId=' + discountId;	
		}

		cj.ajax({
			url: dataUrl,
			async: false,
			global: false,
			success: function ( html ) {
			    cj("#feeBlock").html( html );
			}
    	});
    					
        cj("#feeBlock").ajaxStart(function(){
            cj(".disable-buttons input").attr('disabled', true);
        });
        
        cj("#feeBlock").ajaxStop(function(){
            cj(".disable-buttons input").attr('disabled', false);
        });

        //show event real full as well as waiting list message. 
        if ( cj("#hidden_eventFullMsg").val( ) ) {
          cj( "#eventFullMsg" ).show( ).html( cj("#hidden_eventFullMsg" ).val( ) );
        } else {
          cj( "#eventFullMsg" ).hide( );
        }
	}
</script>
{/literal}
{*include custom data js file*}
{include file="CRM/common/customData.tpl"}
{literal}
<script type="text/javascript">
	cj(function() {				
		{/literal}
		buildCustomData( '{$customDataType}', 'null', 'null' );
		{if $roleID}
		    buildCustomData( '{$customDataType}', {$roleID}, {$roleCustomDataTypeID} );
		{/if}
		{if $eventID}
		    buildCustomData( '{$customDataType}', {$eventID}, {$eventNameCustomDataTypeID} );
		{/if}
		{if $eventTypeID}
		    buildCustomData( '{$customDataType}', {$eventTypeID}, {$eventTypeCustomDataTypeID} );
		{/if}
		
		//call pane js
		cj().crmaccordions();
		{literal}
	});
</script>
{/literal}	

{/if}

    {* include jscript to warn if unsaved form field changes *}
    {include file="CRM/common/formNavigate.tpl"}

{/if} {* end of eventshow condition*}

<script type="text/javascript">
{literal}
	sendNotification();
	cj("#notify").hide();
	function sendNotification( ) {
		 var status = cj("select#status_id option:selected").text();
		 cj("#notify").hide();
		 
		 if ( status == 'Cancelled' || 
     	  	      status == 'Pending from waitlist' || 
		      status == 'Pending from approval' || 
	  	      status == 'Expired' ) {
          	      	  cj("#notify").show();
	  		  cj("#is_notify").attr('checked',true);
   		 }
	}

    function buildEventTypeCustomData( eventID, eventTypeCustomDataTypeID, eventAndTypeMapping ) {
         var mapping = eval('(' + eventAndTypeMapping + ')');
         buildCustomData( 'Participant', mapping[eventID], eventTypeCustomDataTypeID );
    }
{/literal}
</script>
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}

