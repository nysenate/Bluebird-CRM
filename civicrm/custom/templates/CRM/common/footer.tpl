{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
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
  {include file="CRM/common/notifications.tpl"}
{/if}
