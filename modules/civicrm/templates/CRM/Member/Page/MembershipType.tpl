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
{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Member/Form/MembershipType.tpl"}
{else}
    <div id="help">
        <p>{ts}Membership types are used to categorize memberships. You can define an unlimited number of types. Each type incorporates a 'name' (Gold Member, Honor Society Member...), a description, a minimum fee (can be $0), and a duration (can be 'lifetime'). Each member type is specifically linked to the membership entity (organization) - e.g. Bay Area Chapter.{/ts} {docURL page="CiviMember Admin"}</p>
    </div>

    {if $rows}
    <div id="membership_type">
        {strip}
    	{* handle enable/disable actions*}
     	{include file="CRM/common/enableDisable.tpl"}
        {include file="CRM/common/jsortable.tpl"}
     	<table id="options" class="display">
            <thead>
                <tr>
                    <th>{ts}Membership{/ts}</th>
                    <th>{ts}Period{/ts}</th>
                    <th>{ts}Fixed Start{/ts}</th>
                    <th>{ts}Minimum Fee{/ts}</th>
                    <th>{ts}Duration{/ts}</th>
                    <th>{ts}Relationship Type{/ts}</th>   
                    <th>{ts}Visibility{/ts}</th>
                    <th id="order" class="sortable">{ts}Order{/ts}</th>
         	        <th>{ts}Enabled?{/ts}</th>
                    <th></th>
                    <th class="hiddenElement"></th>
                </tr>
            </thead>
            {foreach from=$rows item=row}
               <tr id="row_{$row.id}" class="{cycle values='odd-row,even-row'} {$row.class} crm-membership-type {if NOT $row.is_active} disabled{/if}">
                    <td class="crm-membership-type-type_name">{$row.name}</td>
                    <td class="crm-memberhip-type-period_type">{$row.period_type}</td>
                    <td class="crm-membership-type-fixed_period_start_day">{$row.fixed_period_start_day}</td>
                    <td class="crm-membership-type-minimum_fee" align="right">{$row.minimum_fee|crmMoney}</td>
                    <td class="crm-membership-type-duration_interval_unit">{$row.duration_interval} {$row.duration_unit}</td>
                    <td class="crm-membership-type-relationship_type_name">{$row.relationshipTypeName}</td>
                    <td class="crm-membership-type-visibility">{$row.visibility}</td>
                    <td class="nowrap crm-membership_type-order">{$row.order}</td>
                    <td class="crm-membership-type-status_{$row.id}" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                    <td>{$row.action|replace:'xx':$row.id}</td>
                    <td class="order hiddenElement">{$row.weight}</td>
               </tr>
            {/foreach}
        </table>
        {/strip}

            {if $action ne 1 and $action ne 2}
        	    <div class="action-link">
            	    <a href="{crmURL q="action=add&reset=1"}" id="newMembershipType" class="button"><span><div class="icon add-icon"></div>{ts}Add Membership Type{/ts}</span></a>
                </div>
            {/if}
    </div>
    {else}
      {if $action ne 1}
        <div class="messages status">
       	    <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/>
            {capture assign=crmURL}{crmURL p='civicrm/admin/member/membershipType' q="action=add&reset=1"}{/capture}{ts 1=$crmURL}There are no membership types entered. You can <a href='%1'>add one</a>.{/ts}
        </div>    
      {/if}
    {/if}
{/if}