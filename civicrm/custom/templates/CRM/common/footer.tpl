{*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
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
{*NYSS edits throughout*}
{if call_user_func(array('CRM_Core_Permission','check'), 'access CiviCRM')}
  {include file="CRM/common/bbversion.tpl" assign=bbversion}
  {*include file="CRM/common/accesskeys.tpl"*}
  {*if !empty($contactId)}
    {include file="CRM/common/contactFooter.tpl"}
  {/if*}

  <div class="crm-footer" id="civicrm-footer">
    <div class="bb-footer-left">
      {if $action NEQ 2 AND $contactId}{ts}Contact ID{/ts}:&nbsp;{$contactId}{/if}
      {if !empty($external_identifier)}&nbsp;&nbsp;{ts}External ID{/ts}:&nbsp;{$external_identifier}{/if}
    </div>
    <div class="bb-footer-right">
      {crmVersion assign=version}
    Bluebird v{ts 1=$bbversion}%1.{/ts} Powered by <a href='http://civicrm.org/' target="_blank">CiviCRM</a> {ts 1=$version}%1.{/ts}<br />
    CiviCRM{ts 1='http://www.gnu.org/licenses/agpl-3.0.html'} is openly available under the <a href='%1' target="_blank">GNU Affero General Public License (GNU AGPL)</a>.{/ts}
    </div>
  </div>
  {*include file="CRM/common/notifications.tpl"*}
{/if}
