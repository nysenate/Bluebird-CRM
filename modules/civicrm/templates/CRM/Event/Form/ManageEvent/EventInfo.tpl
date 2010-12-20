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
{* Step 1 of New Event Wizard, and Edit Event Info form. *} 
{if $noEventTemplates}
	{capture assign=etUrl}{crmURL p='civicrm/admin/eventTemplate' q="reset=1"}{/capture}
        <div class="status message">
	<table class="form-layout">
	        <tr><td><div class="icon inform-icon"></div></td>
	            <td class="status">{ts 1=$etUrl}If you find that you are creating multiple events with similar settings, you may want to use the <a href='%1'>Event Templates</a> feature to streamline your workflow.{/ts}</td>
	        </tr>
	</table>
        </div>
{/if}
<div class="crm-block crm-form-block crm-event-manage-eventinfo-form-block">
{if $cdType} 
	{include file="CRM/Custom/Form/CustomData.tpl"} 
{else} 
	{assign var=eventID value=$id}
        <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
        </div>
	<table class="form-layout-compressed">
    	       {if $form.template_id}
			<tr class="crm-event-manage-eventinfo-form-block-template_id">
				<td class="label">{$form.template_id.label}</td>
    				<td>{$form.template_id.html} {help id="id-select-template"}</td>
    			</tr>
    		{/if}
		{if $form.template_title}
			<tr class="crm-event-manage-eventinfo-form-block-template_title">
				<td class="label">{$form.template_title.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_event' field='template_title' id=$eventID}{/if}</td>
				<td>{$form.template_title.html} {help id="id-template-title"}</td>
			</tr>
		{/if}
		<tr class="crm-event-manage-eventinfo-form-block-event_type_id">
			<td class="label">{$form.event_type_id.label}</td>
			<td>{$form.event_type_id.html}<br />
			<span class="description">{ts}After selecting an Event Type, this page will display any custom event fields for that type.{/ts}</td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-default_role_id">
			<td class="label">{$form.default_role_id.label}</td>
			<td>{$form.default_role_id.html}<br />
			<span class="description">{ts}The Role you select here is automatically assigned to people when they register online for this event (usually the default 'Attendee' role).{/ts}
			{help id="id-participant-role"}</td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-participant_listing_id">
			<td class="label">{$form.participant_listing_id.label}</td>
			<td>{$form.participant_listing_id.html}<br />
			<span class="description"> {ts}To allow users to see a listing of participants, set this field to 'Name' (list names only), 'Name and Email', or 'Name, Status and Register Date'.{/ts} 
			{help id="id-listing"} </span></td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-title">
			<td class="label">{$form.title.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_event' field='title' id=$eventID}{/if}</td>
			<td>{$form.title.html}<br />
			<span class="description"> {ts}Please use only alphanumeric, spaces, hyphens and dashes for event names.{/ts} 
			</span></td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-summary">
			<td class="label">{$form.summary.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_event' field='summary' id=$eventID}{/if}</td>
			<td>{$form.summary.html}</td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-description">
			<td class="label">{$form.description.label}</td>
			<td>{$form.description.html}</td>
		</tr>
		{if !$isTemplate}
			<tr class="crm-event-manage-eventinfo-form-block-start_date">
				<td class="label">{$form.start_date.label}</td>
				<td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
			</tr>
			<tr class="crm-event-manage-eventinfo-form-block-end_date">
				<td class="label">{$form.end_date.label}</td>
				<td>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
			</tr>
		{/if}
		<tr class="crm-event-manage-eventinfo-form-block-max_participants">
			<td class="label">{$form.max_participants.label}</td>
			<td>{$form.max_participants.html|crmReplace:class:four} {help id="id-max_participants"}</td>
		</tr>
    <tr id="id-waitlist" class="crm-event-manage-eventinfo-form-block-has_waitlist">
      {if $form.has_waitlist}
        <td class="label">{$form.has_waitlist.label}</td>
        <td>{$form.has_waitlist.html} {help id="id-has_waitlist"}</td>
      {/if}
    </tr>
		<tr id="id-event_full" class="crm-event-manage-eventinfo-form-block-event_full_text">
			<td class="label">{$form.event_full_text.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_event' field='event_full_text' id=$eventID}{/if}<br />{help id="id-event_full_text"}</td>
			<td>{$form.event_full_text.html}</td>
		</tr>
		<tr id="id-waitlist-text" class="crm-event-manage-eventinfo-form-block-waitlist_text">
      {if $form.waitlist_text}
        <td class="label">{$form.waitlist_text.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_event' field='waitlist_text' id=$eventID}{/if}<br />{help id="id-help-waitlist_text"}</td>
        <td>{$form.waitlist_text.html}</td>
      {/if}
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-is_map">
			<td>&nbsp;</td>
			<td>{$form.is_map.html} {$form.is_map.label} {help id="id-is_map"}</td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-is_public">
			<td>&nbsp;</td>
			<td>{$form.is_public.html} {$form.is_public.label}<br />
			<span class="description">{ts}Include this event in iCalendar feeds?{/ts}</span></td>
		</tr>
		<tr class="crm-event-manage-eventinfo-form-block-is_active">
			<td>&nbsp;</td>
			<td>{$form.is_active.html} {$form.is_active.label}</td>
		</tr>

		{if $eventID}
		<tr>
			<td>&nbsp;</td>
			<td class="description">
			{if $config->userFramework EQ 'Drupal'}
				{ts}When this Event is active, create links to the Event Information page by copying and pasting the following URL:{/ts}<br />
				<strong>{crmURL a=true p='civicrm/event/info' q="reset=1&id=`$eventID`"}</strong> 
			{elseif $config->userFramework EQ 'Joomla'}
				{ts 1=$eventID}When this Event is active, create front-end links to the Event Information page using the Menu Manager. Select <strong>Event Info Page</strong> and enter <strong>%1</strong> for the Event ID.{/ts} 
			{/if}
			</td>
		</tr>
		{/if}
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<div id="customData"></div>
	{*include custom data js file*}
	{include file="CRM/common/customData.tpl"}	
	{literal}
		<script type="text/javascript">
			cj(document).ready(function() {
				{/literal}
				{if $customDataSubType} 
					buildCustomData( '{$customDataType}', {$customDataSubType} );
				{else}
					buildCustomData( '{$customDataType}' );
				{/if}
				{literal}
			});
		</script>
	{/literal}
        <div class="crm-submit-buttons">
           {include file="CRM/common/formButtons.tpl" location="bottom"}
        </div>
    {include file="CRM/common/showHide.tpl" elemType="table-row"}

    {* include jscript to warn if unsaved form field changes *}
    {include file="CRM/common/formNavigate.tpl"}
{/if}
</div>
{literal}
<script type="text/javascript">

function reloadWindow( tempId ) {

   //ignore form navigation, CRM-6815
   global_formNavigate = true;

   //freeze the event type element 
   //when template form is loading.
   cj( "#event_type_id" ).attr('disabled', true );

   window.location += '&template_id=' + tempId;
}

</script>
{/literal}

