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
{if $action eq 1 or $action eq 2}
  {include file="CRM/Contact/Form/DedupeRules.tpl"}
{elseif $action eq 4}
{include file="CRM/Contact/Form/DedupeFind.tpl"}
{else}
    <div id="help">
        {ts}Manage the rules used to identify potentially duplicate contact records. Scan for duplicates using a selected rule and merge duplicate contact data as needed.{/ts} {help id="id-dedupe-intro"}
    </div>
    {if $rows}
        {include file="CRM/common/jsortable.tpl"}
        <div id="browseValues">
            {strip}
              <table id="options" class="display">
                <thead>
                <tr>
                  <th>{ts}Name{/ts}</th>
                  <th id="sortable">{ts}Contact Type{/ts}</th>
                  <th>{ts}Level{/ts}</th>
                  <th>{ts}Default?{/ts}</th>
                  <th></th>
                </tr>
                </thead>
                {foreach from=$rows item=row}
                  <tr class="{cycle values="odd-row,even-row"}">
                    <td>{if isset($row.name)}{$row.name}{/if}</td>
                    <td>{$row.contact_type_display}</td>	
                    <td>{$row.level}</td>	
                    {if $row.is_default}
                        <td><img src="{$config->resourceBase}/i/check.gif" alt="{ts}Default{/ts}" /></td>    
                    {else}
                        <td></td>
                    {/if}
                    <td>{$row.action|replace:'xx':$row.id}</td>
                  </tr>
                {/foreach}
              </table>
            {/strip}
        </div>
    {/if}
    {if $hasperm_administer_dedupe_rules}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&contact_type=Individual&reset=1"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Rule for Individuals{/ts}</span></a>
    	<a href="{crmURL q="action=add&contact_type=Household&reset=1"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Rule for Households{/ts}</span></a>
    	<a href="{crmURL q="action=add&contact_type=Organization&reset=1"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Rule for Organizations{/ts}</span></a>
    	<div class="clear"><br /></div>
	    <a href="{crmURL p='civicrm/dedupe/exception' q='reset=1'}" class="button"><span>{ts}View Dedupe Exceptions{/ts}</span></a>
        </div>
    {/if}
{/if}