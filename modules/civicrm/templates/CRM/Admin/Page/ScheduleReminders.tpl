{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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
{* this template is for configuring Scheduled Reminders *}

{if $action eq 1 or $action eq 2 or $action eq 8 or $action eq 16384}
   {include file="CRM/Admin/Form/ScheduleReminders.tpl"}
{else}
{if $rows}
<div id="reminder">
        {strip}
	{include file="CRM/common/enableDisable.tpl"} 
        {include file="CRM/common/jsortable.tpl"}
        <table id="scheduleReminders" class="display">
        <thead>
        <tr id="options" class="columnheader">
            <th class="sortable">{ts}Title{/ts}</th>
            <th >{ts}Reminder For{/ts}</th>
            <th >{ts}When{/ts}</th>
            <th >{ts}While{/ts}</th>
            <th >{ts}Repeat{/ts}</th>
            <th >{ts}Active?{/ts}</th>
            <th class="hiddenElement"></th>
            <th ></th>
        </tr>
        </thead>
        {foreach from=$rows item=row}
        <tr id="row_{$row.id}" class="crm-scheduleReminders {cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
            <td class="crm-scheduleReminders-title">{$row.title}</td>
            <td class="crm-scheduleReminders-value">{$row.entity} - {$row.value}</td>
            <td class="crm-scheduleReminders-description">{$row.first_action_offset}&nbsp;{$row.first_action_unit}&nbsp;{$row.first_action_condition}&nbsp;{$row.entityDate}</td>
            <td class="crm-scheduleReminders-title">{$row.status}</td>
            <td class="crm-scheduleReminders-is_repeat">{if $row.is_repeat eq 1}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}&nbsp;</td>
	    <td id="row_{$row.id}_status" class="crm-scheduleReminders-is_active">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	    <td>{$row.action|replace:'xx':$row.id}</td>
	    <td class="hiddenElement"></td>
        </tr>
        {/foreach}
        </table>
        {/strip}

	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="newScheduleReminder" class="button"><span><div class="icon add-icon"></div>{ts}Add Reminder{/ts}</span></a>
        </div>

</div>
{else}
    <div class="messages status">
      <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL p='civicrm/admin/scheduleReminders' q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no Scheduled Reminders configured. You can <a href='%1'>add one</a>.{/ts}
    </div>    
{/if}
{/if}
