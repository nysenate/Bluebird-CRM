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
<script type="text/javascript" src="{$config->resourceBase}js/rest.js"></script>
{if $showBestResult }
    <span class="font-red">{ts}For best results, the Country field should precede the State-Province field in your Profile form. You can use the up and down arrows on field listing page for this profile to change the order of these fields or manually edit weight for Country/State-Province Field.{/ts}</span>
{/if}    

{if $action eq 1 or $action eq 2 or $action eq 4 or $action eq 8 }
    {include file="CRM/UF/Form/Field.tpl"}
{elseif $action eq 1024 }
    {include file="CRM/UF/Form/Preview.tpl"}
{else}
<div class="crm-content-block">
    {if $ufField}
        <div id="field_page">
        {if not ($action eq 2 or $action eq 1)}
            <div class="action-link">
                <a href="{crmURL p="civicrm/admin/uf/group/field/add" q="reset=1&action=add&gid=$gid"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Field{/ts}</span></a><a href="{crmURL p="civicrm/admin/uf/group/update" q="action=update&id=`$gid`&reset=1&context=field"}" class="button"><span><div class="icon edit-icon"></div>{ts}Edit Settings{/ts}</span></a><a href="{crmURL p="civicrm/admin/uf/group" q="action=preview&id=`$gid`&reset=1&field=0&context=field"}" class="button"><span><div class="icon preview-icon"></div>{ts}Preview (all fields){/ts}</span></a>{if !$skipCreate }<a href="{crmURL p="civicrm/profile/create" q="gid=$gid&reset=1"}" class="button"><span><div class="icon play-icon"></div>{ts}Use (create mode){/ts}</span></a>{/if}
                <div class="clear"></div>
            </div>
        {/if}
        {strip}
        {* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
 	{include file="CRM/common/jsortable.tpl"}
        <table id="options" class="display">
            <thead>
            <tr>
                <th>{ts}Field Name{/ts}</th>
                <th>{ts}Visibility{/ts}</th>
                <th>{ts}Searchable?{/ts}</th>
                <th>{ts}In Selector?{/ts}</th>
                <th id="order" class="sortable">{ts}Order{/ts}</th>
                <th>{ts}Active{/ts}</th>	
                <th>{ts}Required{/ts}</th>	
                <th>{ts}View Only{/ts}</th>	
                <th>{ts}Reserved{/ts}</th>
                <th></th>
		<th class="hiddenElement"></th>
            </tr>
            </thead>
            {foreach from=$ufField item=row}
            <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
                <td>{$row.label}<br/>({$row.field_type})</td>
                <td>{$row.visibility_display}</td>
                <td>{if $row.is_searchable   eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td>{if $row.in_selector     eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td class="nowrap">{$row.order}</td>
                <td id="row_{$row.id}_status">{if $row.is_active eq 1}       {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td>{if $row.is_required     eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td>{if $row.is_view         eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td>{if $row.is_reserved     eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td>{$row.action|replace:'xx':$row.id}</td>
                <td class="order hiddenElement">{$row.weight}</td>
            </tr>
            {/foreach}
        </table>
        {/strip}
        {if not ($action eq 2 or $action eq 1)}
            <div class="action-link">
                <a href="{crmURL p="civicrm/admin/uf/group/field/add" q="reset=1&action=add&gid=$gid"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Field{/ts}</span></a><a href="{crmURL p="civicrm/admin/uf/group" q="action=update&id=`$gid`&reset=1&context=field"}" class="button"><span><div class="icon edit-icon"></div>{ts}Edit Settings{/ts}</span></a><a href="{crmURL p="civicrm/admin/uf/group" q="action=preview&id=`$gid`&reset=1&field=0&context=field"}" class="button"><span><div class="icon preview-icon"></div>{ts}Preview (all fields){/ts}</span></a>{if !$skipCreate }<a href="{crmURL p="civicrm/profile/create" q="gid=$gid&reset=1"}" class="button"><span><div class="icon play-icon"></div>{ts}Use (create mode){/ts}</span></a>{/if}
                <div class="clear"></div>
            </div>
        {/if}    
        
        </div>
    {else}
        {if $action eq 16}
        {capture assign=crmURL}{crmURL p="civicrm/admin/uf/group/field/add" q="reset=1&action=add&gid=$gid"}{/capture}
        <div class="messages status">
        <div class="icon inform-icon"></div>
       {ts 1=$groupTitle 2=$crmURL}There are no CiviCRM Profile Fields for '%1', you can <a href='%2'>add one now</a>.{/ts}
        </div>
        {/if}
    {/if}
</div>
{/if}
