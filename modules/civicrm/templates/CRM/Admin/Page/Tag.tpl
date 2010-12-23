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

{capture assign=docLink}{docURL page="Tags Admin"}{/capture}

{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Tag.tpl"}	
{else}
<div class="crm-content-block">
    <div id="help">
        {ts 1=$docLink}Tags can be assigned to any contact record, and are a convenient way to find contacts. You can create as many tags as needed to organize and segment your records.{/ts} {$docLink}
    </div>

    {if $rows}
        {if !($action eq 1 and $action eq 2)}
            <div class="crm-submit-buttons">
        	    <div class="action-link">
                    <a href="{crmURL q="action=add&reset=1"}" id="newTag" class="button"><span><div class="icon add-icon"></div>{ts}Add Tag{/ts}</span></a>
                    {if $adminTagSet}
                        <a href="{crmURL q="action=add&reset=1&tagset=1"}" id="newTagSet" class="button"><span><div class="icon add-icon"></div>{ts}Add Tag Set{/ts}</span></a>
                    {/if}
                </div>
            </div>
        {/if}

        {include file="CRM/common/jsortable.tpl"}
        <div id="cat">
            {strip}
            <table id="options" class="display">
        	    <thead>
                    <tr>
            	        <th>{ts}Tag{/ts}</th>
                        <th>{ts}ID{/ts}</th>
            	        <th id="nosort">{ts}Description{/ts}</th>
            	        <th>{ts}Parent ID{/ts}</th>
            		    <th>{ts}Used For{/ts}</th>
            		    <th>{ts}Tag set?{/ts}</th>
            		    <th>{ts}Reserved?{/ts}</th>
            	        <th></th>
                    </tr>
                </thead>
                {foreach from=$rows item=row key=id }
                <tr class="{cycle values="odd-row,even-row"} {$row.class} crm-tag">
                    <td class="crm-tag-name">{$row.name}</td>
                    <td class="crm-tag-id">{$row.id}</td>	
                    <td class="crm-tag-description">{$row.description} </td>
                    <td class="crm-tag-parent">{$row.parent} {if $row.parent_id}({$row.parent_id}){/if}</td>
        	        <td>{$row.used_for}</td>
                    <td class="crm-tag-is_tagset">{if $row.is_tagset}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Tag Set{/ts}" />{/if}</td>
                    <td class="crm-tag-is_reserved">{if $row.is_reserved}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Reserved{/ts}" />{/if}</td>
                    <td>{$row.action|replace:'xx':$row.id}</td>
                </tr>
                {/foreach}
            </table>
            {/strip}
        </div>
        {if !($action eq 1 and $action eq 2)}
            <div class="crm-submit-buttons">
                <div class="action-link">
                    <a href="{crmURL q="action=add&reset=1"}" id="newTag" class="button"><span><div class="icon add-icon"></div>{ts}Add Tag{/ts}</span></a>
                    {if $adminTagSet}
                        <a href="{crmURL q="action=add&reset=1&tagset=1"}" id="newTagSet" class="button"><span><div class="icon add-icon"></div>{ts}Add Tag Set{/ts}</span></a>
                    {/if}
                </div>
            </div>
        {/if}
    {else}
        <div class="messages status">
        <div class="icon inform-icon"></div>&nbsp;
            {capture assign=crmURL}{crmURL p='civicrm/admin/tag' q="action=add&reset=1"}{/capture}
            {ts 1=$crmURL}There are no Tags present. You can <a href='%1'>add one</a>.{/ts}
        </div>    
    {/if}
</div>
{/if}