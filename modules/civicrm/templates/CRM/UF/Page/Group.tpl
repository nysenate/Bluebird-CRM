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
{if $action eq 1 or $action eq 2 or $action eq 4 or $action eq 8 or $action eq 64 or $action eq 16384}
    {* Add or edit Profile Group form *}
    {include file="CRM/UF/Form/Group.tpl"}
{elseif $action eq 1024}
    {* Preview Profile Group form *}	
    {include file="CRM/UF/Form/Preview.tpl"}
{elseif $action eq 8192}
    {* Display HTML Form Snippet Code *}
    <div id="help">
        {ts}The HTML code below will display a form consisting of the active fields in this Profile. You can copy this HTML code and paste it into any block or page on ANY website where you want to collect contact information.{/ts} {help id='standalone'}
    </div>
    <br />
    <form name="html_code" action="{crmURL p='civicrm/admin/uf/group' q="action=profile&gid=$gid"}">
    <div id="standalone-form">
        <textarea rows="20" cols="80" name="profile" id="profile">{$profile}</textarea>
        <div class="spacer"></div>    
        <a href="#" onclick="html_code.profile.select(); return false;" class="button"><span>Select HTML Code</span></a> 
    </div>
    <div class="action-link">
        &nbsp; <a href="{crmURL p='civicrm/admin/uf/group' q="reset=1"}">&raquo;  {ts}Back to Profile Listings{/ts}</a>
    </div>
    </form>

{else}
    <div id="help">
        {ts}CiviCRM Profile(s) allow you to aggregate groups of fields and include them in your site as input forms, contact display pages, and search and listings features. They provide a powerful set of tools for you to collect information from constituents and selectively share contact information.{/ts} {help id='profile_overview'}
    </div>

    {if NOT ($action eq 1 or $action eq 2)}
    <div class="crm-submit-buttons">
        <a href="{crmURL p='civicrm/admin/uf/group/add' q="action=add&reset=1"}" id="newCiviCRMProfile-top" class="button"><span><div class="icon add-icon"></div>{ts}Add Profile{/ts}</span></a>
    </div>
    {/if}
    {if $rows}
    <div class="crm-content-block">
    <div id="uf_profile">
        {strip}
        {* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
    {include file="CRM/common/jsortable.tpl"}
      <table id="options" class="display">
        <thead>
          <tr>
            <th id="sortable">{ts}Profile Title{/ts}</th>
            <th>{ts}Type{/ts}</th>
            <th>{ts}ID{/ts}</th>
            <th id="nosort">{ts}Used For{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th>{ts}Reserved{/ts}</th>
            <th></th>
          </tr>
        </thead> 
        <tbody>
        {foreach from=$rows item=row}
	    <tr id="row_{$row.id}"class="{$row.class}{if NOT $row.is_active} disabled{/if}">
            <td>{$row.title}</td>
            <td>{$row.group_type}</td>
            <td>{$row.id}</td>
            <td>{$row.module}</td>
            <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
            <td>{if $row.is_reserved}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
            <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
        </tbody>
        </table>
        
        {if NOT ($action eq 1 or $action eq 2)}
        <div class="crm-submit-buttons">
            <a href="{crmURL p='civicrm/admin/uf/group/add' q='action=add&reset=1'}" id="newCiviCRMProfile-bottom" class="button"><span><div class="icon add-icon"></div>{ts}Add Profile{/ts}</span></a>
        </div>
        {/if}
        {/strip}
    </div>
    </div>
    {else}
    {if $action ne 1} {* When we are adding an item, we should not display this message *}
       <div class="messages status">
         <div class="icon inform-icon"></div> &nbsp;
         {capture assign=crmURL}{crmURL p='civicrm/admin/uf/group/add' q='action=add&reset=1'}{/capture}{ts 1=$crmURL}No CiviCRM Profiles have been created yet. You can <a href='%1'>add one now</a>.{/ts}
       </div>
    {/if}
    {/if}
{/if}