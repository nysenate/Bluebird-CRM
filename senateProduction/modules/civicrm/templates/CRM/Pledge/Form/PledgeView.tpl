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
<h3>{ts}View Pledge{/ts}</h3>
<div class="crm-block crm-content-block crm-pledge-view-block">  
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<table class="crm-info-panel">
     <tr class="crm-pledge-form-block-displayName"><td class="label">{ts}Pledge By{/ts}</td><td class="bold"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$contactId"}">{$displayName}</a></td></tr>
     <tr class="crm-pledge-form-block-amount"><td class="label">{ts}Total Amount{/ts}</td><td class="bold">{$amount|crmMoney}&nbsp;</td></tr>
     <tr class="crm-pledge-form-block-installments"><td class="label">{ts}To be paid in{/ts}</td><td>{$installments}&nbsp;&nbsp;{ts}installments of{/ts} {$eachPaymentAmount|crmMoney}&nbsp;&nbsp;{ts}every{/ts}&nbsp;&nbsp;{$frequency_interval}&nbsp;{$frequencyUnit}</td></tr>
 	 <tr><td class="label">{ts}Payments are due on the{/ts}</td><td>{$frequency_day}&nbsp;day of the period</td></tr>

    {if $start_date}     
    	 <tr class="crm-pledge-form-block-create_date"><td class="label">{ts}Pledge Made{/ts}</td><td>{$create_date|truncate:10:''|crmDate}</td></tr>
      	 <tr class="crm-pledge-form-block-start_date"><td class="label">{ts}Payment Start{/ts}</td><td>{$start_date|truncate:10:''|crmDate}</td></tr>
	{/if}
    {if $end_date}    
       	<tr class="crm-pledge-form-block-end_date"><td class="label">{ts}End Date{/ts}</td><td>{$end_date|truncate:10:''|crmDate}</td></tr>
	{/if}
    {if $cancel_date}
         <tr class="crm-pledge-form-block-cancel_date"><td class="label">{ts}Cancelled Date{/ts}</td><td>{$cancel_date|truncate:10:''|crmDate}</td></tr>
    {/if}
        <tr class="crm-pledge-form-block-contribution_type"><td class="label">{ts}Contribution Type{/ts}</td><td>{$contribution_type}&nbsp;
    {if $is_test}
        {ts}(test){/ts}
    {/if}
        </td></tr>
    {if $acknowledge_date}	
            <tr class="crm-pledge-form-block-acknowledge_date"><td class="label">{ts}Received{/ts}</td><td>{$acknowledge_date|truncate:10:''|crmDate}&nbsp;</td></tr>
	{/if}
    {if $contribution_page}
            <tr class="crm-pledge-form-block-contribution_page"><td class="label">{ts}Self-service Payments Page{/ts}</td><td>{$contribution_page}</td></tr>
    {/if}   
        <tr class="crm-pledge-form-block-pledge_status"><td class="label">{ts}Pledge Status{/ts}</td><td{if $status_id eq 3} class="font-red bold"{/if}>{$pledge_status} </td></tr>
    {if $honor_contact_id}
            <tr class="crm-pledge-form-block-honor_type"><td class="label">{$honor_type}</td><td>{$honor_display}&nbsp;</td></tr>
    {/if}
        <tr class="crm-pledge-form-block-initial_reminder_day"><td class="label">{ts}Initial Reminder Day{/ts}</td><td>{$initial_reminder_day}&nbsp;days prior to schedule date </td></tr>
        <tr class="crm-pledge-form-block-max_reminders"><td class="label">{ts}Maximum Reminders Send{/ts}</td><td>{$max_reminders}&nbsp;</td></tr>
        <tr class="crm-pledge-form-block-additional_reminder_day"><td class="label">{ts}Send additional reminders{/ts}</td><td>{$additional_reminder_day}&nbsp;days after the last one sent</td></tr>
    
    {include file="CRM/Custom/Page/CustomDataView.tpl"}
</table>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>  
 
