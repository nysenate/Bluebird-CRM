{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{include file="CRM/common/version.tpl" assign=version}
{include file="CRM/common/bbversion.tpl" assign=bbversion}
{include file="CRM/Block/RecentlyViewed.extra.tpl"}{*NYSS*}
{include file="CRM/common/accesskeys.tpl"}
{if isset($contactId) and $contactId} {* Display contact-related footer. *}
    <div class="footer" id="record-log">
    <span class="col1">{if isset($external_identifier) and $external_identifier}{ts}External ID{/ts}:&nbsp;{$external_identifier}{/if}{if $action NEQ 2}&nbsp; &nbsp;{ts}CiviCRM ID{/ts}:&nbsp;{$contactId}{/if}</span>
    {if isset($lastModified) and $lastModified}
        {ts}Last Change by{/ts} <a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$lastModified.id`"}">{$lastModified.name}</a> ({$lastModified.date|crmDate}) &nbsp;
	{if $changeLog != '0'}
	    <a href="{crmURL p='civicrm/contact/view' q="reset=1&action=browse&selectedChild=log&cid=`$contactId`"}">&raquo; {ts}View Change Log{/ts}</a>
	{/if}
    {/if}
    </div>
{/if}

<div class="footer" id="civicrm-footer">{*NYSS*}
Bluebird v{ts 1=$bbversion}%1.{/ts} Powered by <a href='http://civicrm.org/' target="_blank">CiviCRM</a> {ts 1=$version}%1.{/ts}<br />
CiviCRM{ts 1='http://www.gnu.org/licenses/agpl-3.0.html'} is openly available under the <a href='%1' target="_blank">GNU Affero General Public License (GNU AGPL)</a>.{/ts}
</div>
