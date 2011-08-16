{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
<tr> 
    <td><label>{ts}Membership Type(s){/ts}</label><br />
                   <div class="listing-box">
                    {foreach from=$form.member_membership_type_id item="membership_type_val"} 
                    <div class="{cycle values="odd-row,even-row"}">
                    {$membership_type_val.html}
                    </div>
                    {/foreach}
                </div>
    </td>
    <td><label>{ts}Membership Status{/ts}</label><br />
                <div class="listing-box">
                    {foreach from=$form.member_status_id item="membership_status_val"} 
                    <div class="{cycle values="odd-row,even-row"}">
                    {$membership_status_val.html}
                    </div>
                    {/foreach}
                </div>
    </td>
</tr>

<tr>
    <td>
     {$form.member_source.label}
     <br />{$form.member_source.html}
    </td>
    <td>
     {$form.member_is_primary.html} 
     <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('member_is_primary', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>     
     {help id="id-member_is_primary" file="CRM/Member/Form/Search.hlp"}
     <br />
     {$form.member_pay_later.html}&nbsp;{$form.member_pay_later.label}<br />
     {$form.member_test.html}&nbsp;{$form.member_test.label}<br />
     {$form.member_auto_renew.html}&nbsp;{$form.member_auto_renew.label}
    </td> 
</tr>
<tr> 
    <td> 
     {$form.member_join_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_join_date_low}
    </td>
    <td> 
     {$form.member_join_date_high.label} <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_join_date_high}
    </td> 
</tr> 
<tr> 
    <td> 
     {$form.member_start_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_start_date_low}
    </td>
    <td>
     {$form.member_start_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_start_date_high}
    </td> 
</tr> 
<tr> 
    <td>  
     {$form.member_end_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_end_date_low}
    </td>
    <td> 
     {$form.member_end_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=member_end_date_high}
    </td> 
</tr> 

{* campaign in membership search *}
{include file="CRM/Campaign/Form/addCampaignToComponent.tpl" campaignContext="componentSearch" 
campaignTrClass='' campaignTdClass=''}

{if $membershipGroupTree}
<tr>
    <td colspan="4">
    {include file="CRM/Custom/Form/Search.tpl" groupTree=$membershipGroupTree showHideLinks=false}
    </td>
</tr>
{/if}
