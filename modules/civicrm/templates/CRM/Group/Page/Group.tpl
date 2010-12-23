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
{* Actions: 1=add, 2=edit, browse=16, delete=8 *}
{if $action ne 1 and $action ne 2 and $action ne 8 and $groupPermission eq 1}
<div class="crm-submit-buttons">
    <a accesskey="N" href="{crmURL p='civicrm/group/add' q='reset=1'}" id="newGroup" class="button"><span><div class="icon add-icon"></div>{ts}Add Group{/ts}</span></a><br/>
</div>
{/if} {* action ne add or edit *}
{if $action ne 2 AND $action ne 8}	
{include file="CRM/Group/Form/Search.tpl"}
{/if}
<div class="crm-block crm-content-block">
{if $action eq 16}
<div id="help">
    {ts}Use Groups to organize contacts (e.g. these contacts are part of our 'Steering Committee'). You can also create 'smart' groups based on contact characteristics (e.g. this group consists of all people in our database who live in a specific locality).{/ts} {help id="manage_groups"}
</div>
{/if}

{if $action eq 1 or $action eq 2} 
   {include file="CRM/Group/Form/Edit.tpl"}
{elseif $action eq 8}
   {include file="CRM/Group/Form/Delete.tpl"}
{/if}

<div class="crm-block crm-results-block">
{if $rows}
    <div id="group">
    {if $action eq 16 or $action eq 32 or $action eq 64} {* browse *}  
    {include file="CRM/common/pager.tpl" location="top"}
    {include file="CRM/common/pagerAToZ.tpl"}
       {strip}
       {* handle enable/disable actions*}
       {include file="CRM/common/enableDisable.tpl"}
       {include file="CRM/common/jsortable.tpl"}
       <table id="options" class="display">
        <thead>
          <tr>
          <th id="sortable">{ts}Name{/ts}</th>
          <th>{ts}ID{/ts}</th>
          <th id="nosort">{ts}Description{/ts}</th>
          <th>{ts}Group Type{/ts}</th>
          <th>{ts}Visibility{/ts}</th>
          {if $groupOrg}
              <th>{ts}Organizaton{/ts}</th>	
          {/if}
          <th></th>
         </tr>
        </thead>
       {foreach from=$rows item=row}
       <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
            <td>{$row.title}</td>	
            <td>{$row.id}</td>
            <td>{$row.description|mb_truncate:80:"...":true}</td>
            <td>{$row.group_type}</td>	
            <td>{$row.visibility}</td>
            {if $groupOrg}
    	    <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.org_id`"}">{$row.org_name}</a></td>
            {/if}
            <td>{$row.action|replace:'xx':$row.id}</td>
         </tr>
       {/foreach}
       </table>
       {/strip}
    {include file="CRM/common/pager.tpl" location="bottom"}
    {/if}{* browse action *}
    </div>

{* No groups to list. Check isSearch flag to see if we're in a search or not. Display 'add group' prompt if user has 'edit groups' permission. *}
{elseif $isSearch eq 1 OR $groupExists}
    <div class="status messages">
             <div class="icon inform-icon"></div>
             {capture assign=browseURL}{crmURL p='civicrm/group' q="reset=1"}{/capture}
             {ts}No matching Groups found for your search criteria. Suggestions:{/ts}
             <div class="spacer"></div>
              <ul>
                <li>{ts}Check your spelling.{/ts}</li>
                <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
                <li>{ts}Make sure you have enough privileges in the access control system.{/ts}</li>
              </ul>
             {ts 1=$browseURL}Or you can <a href='%1'>browse all available Groups</a>.{/ts}
     </div>
{elseif $action ne 1 and $action ne 2 and $action ne 8}
    <div class="status messages">
            <div class="icon inform-icon"></div>
            {capture assign=crmURL}{crmURL p='civicrm/group/add' q="reset=1"}{/capture}
            {ts}No Groups have been created for this site.{/ts}
                {if $groupPermission eq 1}
                    {ts 1=$crmURL}You can <a href='%1'>add one</a> now.{/ts}
                {/if} 
    </div>
{/if}
</div>
{if $action ne 1 and $action ne 2 and $action ne 8 and $groupPermission eq 1}
<div class="crm-submit-buttons">
        <a accesskey="N" href="{crmURL p='civicrm/group/add' q='reset=1'}" id="newGroup" class="button"><span><div class="icon add-icon"></div>{ts}Add Group{/ts}</span></a><br/>
</div>
{/if} {* action ne add or edit *}
</div>
