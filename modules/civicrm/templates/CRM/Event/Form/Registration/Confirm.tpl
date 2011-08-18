{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{if $action & 1024}
    {include file="CRM/Event/Form/Registration/PreviewHeader.tpl"}
{/if}

{include file="CRM/common/TrackingFields.tpl"}

<div class="crm-block crm-event-confirm-form-block">
    {if $isOnWaitlist}
        <div class="help">
            {ts}Please verify the information below. <span class="bold">Then click 'Continue' to be added to the WAIT LIST for this event</span>. If space becomes available you will receive an email with a link to a web page where you can complete your registration.{/ts}
        </div>
    {elseif $isRequireApproval}
        <div class="help">
            {ts}Please verify the information below. Then click 'Continue' to submit your registration. <span class="bold">Once approved, you will receive an email with a link to a web page where you can complete the registration process.</span>{/ts}
        </div>
    {else}
        <div id="help">
        {ts}Please verify the information below. Click <strong>Go Back</strong> if you need to make changes.{/ts}
        {if $contributeMode EQ 'notify' and !$is_pay_later and ! $isAmountzero }
            {if $paymentProcessor.payment_processor_type EQ 'Google_Checkout'}
                {ts 1=$paymentProcessor.processorName}Click the <strong>%1</strong> button to checkout to Google, where you will select your payment method and complete the registration.{/ts}
            {else} 	
                {ts 1=$paymentProcessor.processorName}Click the <strong>Continue</strong> button to checkout to %1, where you will select your payment method and complete the registration.{/ts}
            {/if }
        {else}
            {ts}Otherwise, click the <strong>Continue</strong> button below to complete your registration.{/ts}
        {/if}
        </div>
        {if $is_pay_later and !$isAmountzero}
            <div class="bold">{$pay_later_receipt}</div>
        {/if}
    {/if}

    {if $event.confirm_text}
        <div id="intro_text" class="crm-section event_confirm_text-section">
	        <p>{$event.confirm_text}</p>
        </div>
    {/if}
    
    <div class="crm-group event_info-group">
        <div class="header-dark">
            {ts}Event Information{/ts}
        </div>
        <div class="display-block">
            {include file="CRM/Event/Form/Registration/EventInfoBlock.tpl"}
        </div>
    </div>
    
    {if $paidEvent} 
        <div class="crm-group event_fees-group">
            <div class="header-dark">
                {$event.fee_label}
            </div>
            {if $lineItem}
                {include file="CRM/Price/Page/LineItem.tpl" context="Event"}
            {elseif $amount || $amount == 0}
			    <div class="crm-section no-label amount-item-section">
                    {foreach from= $amount item=amount key=level}  
    					<div class="content">
    					    {$amount.amount|crmMoney}&nbsp;&nbsp;{$amount.label}
    					</div>
            			<div class="clear"></div>
                    {/foreach}
    		    </div>	
                {if $totalAmount}
        			<div class="crm-section no-label total-amount-section">
                		<div class="content bold">{ts}Total Amount{/ts}:&nbsp;&nbsp;{$totalAmount|crmMoney}</div>
                		<div class="clear"></div>
                	</div>
                {/if}	 		
                {if $hookDiscount.message}
                    <div class="crm-section hookDiscount-section">
                        <em>({$hookDiscount.message})</em>
                    </div>
                {/if}
            {/if}
        </div>
    {/if}
	
    <div class="crm-group registered_email-group">
        <div class="header-dark">
        	{ts}Registered Email{/ts}
        </div>
        <div class="crm-section no-label registered_email-section">
            <div class="content">{$email}</div>
		    <div class="clear"></div>
		</div>
    </div>
    
    {if $event.participant_role neq 'Attendee' and $defaultRole}
        <div class="crm-group participant_role-group">
            <div class="header-dark">
                {ts}Participant Role{/ts}
            </div>
            <div class="crm-section no-label participant_role-section">
                <div class="content">
                    {$event.participant_role}
                </div>
            	<div class="clear"></div>
            </div>
        </div>
    {/if}


    {if $customPre}
            <fieldset class="label-left">
                {include file="CRM/UF/Form/Block.tpl" fields=$customPre}
            </fieldset>
    {/if}
    
    {if $customPost}
            <fieldset class="label-left">  
                {include file="CRM/UF/Form/Block.tpl" fields=$customPost}
            </fieldset>
    {/if}

    {*display Additional Participant Profile Information*}
    {if $addParticipantProfile}
        {foreach from=$addParticipantProfile item=participant key=participantNo}
            <div class="crm-group participant_info-group">
                <div class="header-dark">
                    {ts 1=$participantNo+1}Participant Information - Participant %1{/ts}	
                </div>
                {if $participant.additionalCustomPre}
                    <fieldset class="label-left"><div class="header-dark">{$participant.additionalCustomPreGroupTitle}</div>
                        {foreach from=$participant.additionalCustomPre item=value key=field}
                            <div class="crm-section {$field}-section">
                                <div class="label">{$field}</div>
                                <div class="content">{$value}</div>
                                <div class="clear"></div>
                            </div>
                        {/foreach}
                    </fieldset>
                {/if}

                {if $participant.additionalCustomPost}
		{foreach from=$participant.additionalCustomPost item=value key=field}
                 <fieldset class="label-left"><div class="header-dark">{$participant.additionalCustomPostGroupTitle.$field.groupTitle}</div>
                        {foreach from=$participant.additionalCustomPost.$field item=value key=field}
                            <div class="crm-section {$field}-section">
                                <div class="label">{$field}</div>
                                <div class="content">{$value}</div>
                                <div class="clear"></div>
                            </div>
                        {/foreach}		 
		{/foreach}		

                    </fieldset>
                {/if}
            </div>
        <div class="spacer"></div>
        {/foreach}
    {/if}

    {if $contributeMode ne 'notify' and !$is_pay_later and $paidEvent and !$isAmountzero and !$isOnWaitlist and !$isRequireApproval}
	    <div class="crm-group billing_name_address-group">
            <div class="header-dark">
                {ts}Billing Name and Address{/ts}
            </div>
        	<div class="crm-section no-label billing_name-section">
        		<div class="content">{$billingName}</div>
        		<div class="clear"></div>
        	</div>
        	<div class="crm-section no-label billing_address-section">
        		<div class="content">{$address|nl2br}</div>
        		<div class="clear"></div>
        	</div>
    	</div>
    {/if}
    
    {if $contributeMode eq 'direct' and ! $is_pay_later and !$isAmountzero and !$isOnWaitlist and !$isRequireApproval}
        <div class="crm-group credit_card-group">
            <div class="header-dark">
                {ts}Credit Card Information{/ts}
            </div>
            <div class="crm-section no-label credit_card_details-section">
                <div class="content">{$credit_card_type}</div>
        		<div class="content">{$credit_card_number}</div>
        		<div class="content">{ts}Expires{/ts}: {$credit_card_exp_date|truncate:7:''|crmDate}</div>
        		<div class="clear"></div>
        	</div>
        </div>
    {/if}
    
    {if $contributeMode NEQ 'notify'} {* In 'notify mode, contributor is taken to processor payment forms next *}
    <div class="messages status section continue_message-section">
        <p>
        {ts}Your registration will not be submitted until you click the <strong>Continue</strong> button. Please click the button one time only.{/ts}
        </p>
    </div>
    {/if}    
   
    {if $paymentProcessor.payment_processor_type EQ 'Google_Checkout' and $paidEvent and !$is_pay_later and ! $isAmountzero and !$isOnWaitlist and !$isRequireApproval}
        <fieldset><legend>{ts}Checkout with Google{/ts}</legend>
            <div class="crm-section google_checkout-section">
                <table class="form-layout-compressed">
            	    <tr>
            		    <td class="description">{ts}Click the Google Checkout button to continue.{/ts}</td>
            	    </tr>
            	    <tr>
            		    <td>{$form._qf_Confirm_next_checkout.html} <span style="font-size:11px; font-family: Arial, Verdana;">Checkout securely.  Pay without sharing your financial information. </span></td>
            	    </tr>
                </table>
            </div>
        </fieldset>    
    {/if}

    <div id="crm-submit-buttons" class="crm-submit-buttons">
	    {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

    {if $event.confirm_footer_text}
        <div id="footer_text" class="crm-section event_confirm_footer-section">
            <p>{$event.confirm_footer_text}</p>
        </div>
    {/if}
</div>
{include file="CRM/common/showHide.tpl"}
