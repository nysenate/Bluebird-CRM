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
{* this template is used for adding/editing/deleting membership type  *}
<h3>{if $action eq 1}{ts}New Membership Type{/ts}{elseif $action eq 2}{ts}Edit Membership Type{/ts}{else}{ts}Delete Membership Type{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-membership-type-form-block">

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<div class="form-item" id="membership_type_form">
    {if $action eq 8}   
         <div class="messages status">
           {ts}WARNING: Deleting this option will result in the loss of all membership records of this type.{/ts} {ts}This may mean the loss of a substantial amount of data, and the action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
         </div>
         <div> {include file="CRM/common/formButtons.tpl"}</div>
     {else}
           <table class="form-layout-compressed">
              <tr class="crm-membership-type-form-block-name">
        	      <td class="label">{$form.name.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_membership_type' field='name' id=$membershipTypeId}{/if}
                  </td>
                  <td>{$form.name.html}<br />
                      <span class="description">{ts}e.g. 'Student', 'Senior', 'Honor Society'...{/ts}</span>
                  </td>
              </tr>
              <tr class="crm-membership-type-form-block-description">
                  <td class="label">{$form.description.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_membership_type' field='description' id=$membershipTypeId}{/if}
                  </td>
                  <td>{$form.description.html}<br />
                      <span class="description">{ts}Description of this membership type for display on signup forms. May include eligibility, benefits, terms, etc.{/ts}</span>
                  </td>
              </tr>
              {if !$searchDone or !$searchCount or !$searchRows}
                  <tr class="crm-membership-type-form-block-member_org">
                      <td class="label">{$form.member_org.label}<span class="marker"> *</span></td>
                      <td><label>{$form.member_org.html}</label>&nbsp;&nbsp;{$form._qf_MembershipType_refresh.html}<br />
                          <span class="description">{ts}Members assigned this membership type belong to which organization (e.g. this is for membership in 'Save the Whales - Northwest Chapter'). NOTE: This organization/group/chapter must exist as a CiviCRM Organization type contact.{/ts}</span>
                      </td>
                  </tr>
              {/if} 
          </table>
        
         <div class="spacer"></div>	
            {if $searchDone} {* Search button clicked *}
              {if $searchCount}
                    {if $searchRows} {* we've got rows to display *}
                        <fieldset><legend>{ts}Select Target Contact for the Membership-Organization{/ts}</legend>
                          <table class="form-layout-compressed"> 
                             <tr class="crm-membership-type-form-block-member_org" >
                                <td class="label">{$form.member_org.label}</td>
                                <td>{$form.member_org.html}&nbsp;&nbsp;{$form._qf_MembershipType_refresh.html}<br />
                                   <span class="description">{ts}Organization, who is the owner for this membership type.{/ts}</span>
        	                    </td> 
                            </tr> 
        	              </table>
                          <div class="spacer"></div>
              
                          <div class="description">
                            {ts}Select the target contact for this membership-organization if it appears below. Otherwise you may modify the search name above and click Search again.{/ts}
                          </div>          
                         {strip}
                         <table>
                            {*Column Headers*}
                             <tr class="columnheader">
                                <td>&nbsp;</td>
                                <td>{ts}Name{/ts}</td>
                                <td>{ts}City{/ts}</td>
                                <td>{ts}State{/ts}</td>
                                <td>{ts}Email{/ts}</td>
                                <td>{ts}Phone{/ts}</td>
                                </tr>
                            {*Data to be displyed*}
                            {foreach from=$searchRows item=row}
                             <tr class="{cycle values="odd-row,even-row"}">
                                <td>{$form.contact_check[$row.id].html}</td>
                                <td>{$row.type} {$row.name}</td>
                                <td>{$row.city}</td>
                                <td>{$row.state}</td>
                                <td>{$row.email}</td>
                                <td>{$row.phone}</td>
                             </tr>
                            {/foreach}
                         </table>
                         {/strip}
                    </fieldset>{*End of Membership Organization Block*}
                {else} {* too many results - we're only displaying 50 *}
                     <table  class="form-layout-compressed">
                        <tr>
                          <td>
                          {capture assign=infoMessage}{ts}Too many matching results. Please narrow your search by entering a more complete target contact name.{/ts}{/capture}
                        {include file="CRM/common/info.tpl"}
                          </td>
                        </tr>
                     </table>
                {/if}
         {else} {* no valid matches for name + contact_type *}
              <table  class="form-layout-compressed">
                 <tr> {capture assign=infoMessage}{ts 1=$form.member_org.value 2=Organization}No matching results for <ul><li>Name like: %1</li><li>Contact type: %2</li></ul>Check your spelling, or try fewer letters for the target contact name.{/ts}{/capture}
            {include file="CRM/common/info.tpl"}           
	         </tr>
	      </table>
         {/if} {* end if searchCount *}
     {/if} {* end if searchDone *}
      <table class="form-layout-compressed">
             <tr class="crm-membership-type-form-block-minimum_fee">
                 <td class="label">{$form.minimum_fee.label}</td>
                 <td>{$form.minimum_fee.html|crmMoney}<br />
                    <span  class="description">{ts}Minimum fee required for this membership type. For free/complimentary memberships - set minimum fee to zero (0).{/ts}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-contribution_type_id">
           	     <td class="label">{$form.contribution_type_id.label}<span class="marker"> *</span></td>
                 <td>{$form.contribution_type_id.html}<br />
                    <span class="description">{ts}Select the contribution type assigned to fees for this membership type (for example 'Membership Fees'). This is required for all membership types - including free or complimentary memberships.{/ts}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-duration_unit_interval">        
                 <td class="label">{$form.duration_unit.label}<span class="marker">*</span></td>
                 <td>{$form.duration_interval.html}&nbsp;&nbsp;{$form.duration_unit.html}<br />
                     <span class="description">{ts}Duration of this membership (e.g. 30 days, 2 months, 5 years, 1 lifetime){/ts}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-period_type"> 
                 <td class="label">{$form.period_type.label}<span class="marker"> *</span></td>
                 <td>{$form.period_type.html}<br />
                      <span class="description">{ts}Select 'rolling' if membership periods begin at date of signup. Select 'fixed' if membership periods begin on a set calendar date.{/ts} {help id="period-type"}</span>
                 </td>
             </tr>   
             <tr id="fixed_start_day_row" class="crm-membership-type-form-block-fixed_period_start_day">
                 <td class="label">{$form.fixed_period_start_day.label}</td>
                 <td>{$form.fixed_period_start_day.html}<br />
                     <span class="description">{ts}Month and day on which a <strong>fixed</strong> period membership or subscription begins. Example: A fixed period membership with Start Day set to Jan 01 means that membership periods would be 1/1/06 - 12/31/06 for anyone signing up during 2006.{/ts}</span>
                 </td>
             </tr>
             <tr id="fixed_rollover_day_row" class="crm-membership-type-form-block-fixed_period_rollover_day">
                 <td class="label">{$form.fixed_period_rollover_day.label}</td>
                 <td>{$form.fixed_period_rollover_day.html}<br />
                     <span class="description">{ts}Membership signups after this date cover the following calendar year as well. Example: If the rollover day is November 31, membership period for signups during December will cover the following year.{/ts}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-relationship_type_id"> 	
                 <td class="label">{$form.relationship_type_id.label}</td>
                 <td>{$form.relationship_type_id.html}<br />
                     <span class="description">{ts}Memberships can be automatically granted to related contacts by selecting a Relationship Type.{/ts} {help id="rel-type"}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-visibility">
                 <td class="label">{$form.visibility.label}</td>
                 <td>{$form.visibility.html}<br />
                     <span class="description">{ts}Is this membership type available for self-service signups ('Public') or assigned by CiviCRM 'staff' users only ('Admin'){/ts}</span>
                 </td>
             </tr>
             <tr class="crm-membership-type-form-block-weight">
                 <td class="label">{$form.weight.label}</td>
                 <td>{$form.weight.html}</td>
             </tr>
             <tr class="crm-membership-type-form-block-is_active">
                 <td class="label">{$form.is_active.label}</td>
                 <td>{$form.is_active.html}</td>
             </tr>
         </table>
        <div class="spacer"></div>
        <fieldset><legend>{ts}Renewal Reminders{/ts}</legend>
        <div class="description">
            {ts}If you would like Membership Renewal Reminder emails sent to members automatically, you need to create a reminder message template and you need to configure and periodically run a 'cron' job on your server.{/ts} {docURL page="Membership Types"}
        </div>
        {if $noMsgTemplates}
            {capture assign=msgTemplate}{crmURL p='civicrm/admin/messageTemplates' q="action=add&reset=1"}{/capture}
            <div class="status message">
                {ts 1=$msgTemplate}No message templates have been created yet. If you want renewal reminders to be sent, <a href='%1'>click here</a> to create a reminder email template. Then return to this screen to assign the renewal reminder message, and set reminder date.{/ts}
            </div>
        {else}
            <table class="form-layout-compressed">
                <tr class="crm-membership-type-form-block-renewal_msg_id" >
                    <td class="label">{$form.renewal_msg_id.label}</td>
                    <td>{$form.renewal_msg_id.html}<br />        
                        <span class="description">{ts}Select the renewal reminder message to be sent to the members of this membership type.{/ts}</span>
                    </td>
                </tr>
                <tr class="crm-membership-type-form-block-renewal_reminder_day">              
                    <td class="label">{$form.renewal_reminder_day.label}</td>
                    <td>{$form.renewal_reminder_day.html}<br />
                        <span class="description">{ts}Send Reminder these many days prior to membership expiration.{/ts}</span>
                    </td>
                </tr>
            </table>
        {/if}
    </fieldset>

    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    {/if}
    <div class="spacer"></div>
</div>
</div>
{literal}
    <script type="text/javascript">
    if ( ( document.getElementsByName("period_type")[0].value   == "fixed" ) && 
         ( document.getElementsByName("duration_unit")[0].value == "year"  ) ) {
 	        show('fixed_start_day_row', 'table-row');
 	        show('fixed_rollover_day_row', 'table-row');
    } else {
        hide('fixed_start_day_row', 'table-row');
        hide('fixed_rollover_day_row', 'table-row');
    }
	function showHidePeriodSettings(){
        if ( ( document.getElementsByName("period_type")[0].value   == "fixed" ) && 
             ( document.getElementsByName("duration_unit")[0].value == "year"  ) ) {
	        show('fixed_start_day_row', 'table-row');
	        show('fixed_rollover_day_row', 'table-row');
		    document.getElementsByName("fixed_period_start_day[M]")[0].value = "1";
		    document.getElementsByName("fixed_period_start_day[d]")[0].value = "1";
            document.getElementsByName("fixed_period_rollover_day[M]")[0].value = "12";
		    document.getElementsByName("fixed_period_rollover_day[d]")[0].value = "31";
        } else {
            hide('fixed_start_day_row', 'table-row');
            hide('fixed_rollover_day_row', 'table-row');
            document.getElementsByName("fixed_period_start_day[M]")[0].value = "";
		    document.getElementsByName("fixed_period_start_day[d]")[0].value = "";
		    document.getElementsByName("fixed_period_rollover_day[M]")[0].value = "";
		    document.getElementsByName("fixed_period_rollover_day[d]")[0].value = "";
	    }
    }
    </script>
{/literal}
