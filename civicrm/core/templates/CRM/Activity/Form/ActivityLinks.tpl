{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
{* Links for scheduling/logging meetings and calls and Sending Email *}
{if $cdType eq false }
{if $contact_id }
{assign var = "contactId" value= $contact_id }
{/if}

{if $as_select} {* on 3.2, the activities can be either a drop down select (on the activity tab) or a list (on the action menu) *}
<select onchange="if (this.value) window.location='{$url}'+ this.value; else return false" name="other_activity" id="other_activity" class="form-select">
  <option value="">{ts}- new activity -{/ts}</option>
{foreach from=$activityTypes key=k item=link}
  <option value="{$k}">{$link}</option>
{/foreach}
</select>

{else}
<ul>
{foreach from=$activityTypes key=k item=link}
<li class="crm-activity-type_{$k}"><a href="{$url}{$k}">{if $k NEQ 3}{ts}Record {/ts}{/if}{$link}</a></li>
{/foreach}</ul>

{/if}


{* add hook links if any *}
{if $hookLinks}
   {foreach from=$hookLinks item=link}
{if $link.img}
      <a href="{$link.url}"><img src="{$link.img}" alt="{$link.title}" /></a>&nbsp;
{/if}
      <a href="{$link.url}">{$link.title}</a>&nbsp;&nbsp;
   {/foreach}
{/if}

{/if}
