{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
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
{if $sms}
  {assign var='newMassUrl' value='civicrm/sms/send'}
  {assign var='qVal' value='&sms=1'}
  {assign var='linkTitle' value='New SMS'}
  {assign var='componentName' value='Mass SMS'}
{else}
  {assign var='newMassUrl' value='civicrm/mailing/send'}
  {assign var='qVal' value=''}
  {assign var='linkTitle' value='New Mailing'}
  {assign var='componentName' value='Mailings'}
{/if}

{if $showLinks}
    <div class="action-link">
      {crmButton accesskey="N"  p=$newMassUrl q='reset=1' icon="envelope"}{ts}{$linkTitle}{/ts}{/crmButton}<br/><br/>
    </div>
{/if}
{include file="CRM/Mailing/Form/Search.tpl"}

<div class="crm-block crm-content-block"> {*NYSS*}
{if $rows}
    {include file="CRM/common/pager.tpl" location="top"}
    {include file="CRM/common/pagerAToZ.tpl"}

    {strip}
    {*NYSS 12029*}
    <div id="actionDialog" class="crm-container" style="display:none;"></div>
    <table class="selector row-highlight">
      <thead class="sticky">
      {foreach from=$columnHeaders item=header}
        <th>
          {if $header.sort}
            {assign var='key' value=$header.sort}
            {$sort->_response.$key.link}
          {else}
            {$header.name}
          {/if}
        </th>
      {/foreach}
      </thead>

      {*NYSS 10955/CRM-20781 truncate some fields if too long*}
      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
      <tr id="crm-mailing_{$row.id}" class="{cycle values="odd-row,even-row"} crm-mailing crm-mailing_status-{$row.status}">
        <td class="crm-mailing-name">{$row.name|replace:'_':' '}</td>
        {if $multilingual}
          <td class="crm-mailing-language">{$row.language}</td>
        {/if}
        <td class="crm-mailing-subject">{$row.subject}</td>{*NYSS 6007*}
        <td class="crm-mailing-created_date">{$row.created_date}</td>
        <td class="crm-mailing-status crm-mailing_status-{$row.status}">{$row.status}</td>
        <td class="crm-mailing-scheduled_by">
          <a href ={crmURL p='civicrm/contact/view' q="reset=1&cid="}{$row.scheduled_id} title="{$row.scheduled_by|escape}">
            {$row.scheduled_by|mb_truncate:20:"..."}
          </a>
        </td>
        <td class="crm-mailing-scheduled">
          <a href="#" class="crm-summary-link mail-merge-hover">
            {$row.scheduled}
            <div class="crm-tooltip-wrapper">
              <div class="crm-tooltip">
                {if $row.start}
                  Started {$row.start}<br/>
                  {if $row.end}
                    Completed {$row.end}
                  {else}
                    but not yet completed
                  {/if}
                {else}
                  Mailing job has not started
                {/if}
              </div>
            </div>
          </a>
        </td>
        {if call_user_func(array('CRM_Campaign_BAO_Campaign','isCampaignEnable'))}
          <td class="crm-mailing-campaign">{$row.campaign}</td>
      {/if}
        <td>{$row.action|replace:'xx':$row.id}</td>
      </tr>
      {/foreach}
    </table>
    {/strip}

    {include file="CRM/common/pager.tpl" location="bottom"}
    {if $showLinks}
      <div class="action-link">
            {crmButton accesskey="N"  p=$newMassUrl q='reset=1' icon="envelope"}{ts}{$linkTitle}{/ts}{/crmButton}<br/>
      </div>
    {/if}

{* No mailings to list. Check isSearch flag to see if we're in a search or not. *}
{elseif $isSearch eq 1}
    {if $archived}
        {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/archived' q="reset=1"}{$qVal}{/capture}
        {assign var="browseType" value="Archived"}
    {elseif $unscheduled}
        {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/unscheduled' q="scheduled=false&reset=1"}{$qVal}{/capture}
        {assign var="browseType" value="Draft and Unscheduled"}
    {else}
        {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/scheduled' q="scheduled=true&reset=1"}{$qVal}{/capture}
        {assign var="browseType" value="Scheduled and Sent"}
    {/if}
    <div class="status messages">
        <table class="form-layout">
            <tr><div class="icon inform-icon"></div>
               {ts 1=$componentName}No %1 match your search criteria. Suggestions:{/ts}
      </tr>
                <div class="spacer"></div>
                <ul>
                <li>{ts}Check your spelling.{/ts}</li>
                <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
                </ul>
            <tr>{ts 1=$browseURL 2=$browseType 3=$componentName}Or you can <a href='%1'>browse all %2 %3</a>.{/ts}</tr>
        </table>
    </div>
{elseif $unscheduled}

    <div class="messages status no-popup">
            <div class="icon inform-icon"></div>&nbsp;
            {capture assign=crmURL}{crmURL p=$newMassUrl q='reset=1'}{/capture}
            {ts 1=$componentName}There are no Unscheduled %1.{/ts}
      {if $showLinks}{ts 1=$crmURL}You can <a href='%1'>create and send one</a>.{/ts}{/if}
   </div>

{elseif $archived}
    <div class="messages status no-popup">
            <div class="icon inform-icon"></div>&nbsp
            {capture assign=crmURL}{crmURL p='civicrm/mailing/browse/scheduled' q='scheduled=true&reset=1'}{$qVal}{/capture}
            {ts 1=$crmURL 2=$componentName}There are no Archived %2. You can archive %2 from <a href='%1'>Scheduled or Sent %2</a>.{/ts}
   </div>
{else}
  {*NYSS 5391 combine isSearch and unscheduled; genericize the text*}
  {if $archived}
    {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/archived' q="reset=1"}{$qVal}{/capture}
    {assign var="browseType" value="Archived"}
  {elseif $unscheduled}
    {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/unscheduled' q="scheduled=false&reset=1"}{$qVal}{/capture}
    {assign var="browseType" value="Draft and Unscheduled"}
  {else}
    {capture assign=browseURL}{crmURL p='civicrm/mailing/browse/scheduled' q="scheduled=true&reset=1"}{$qVal}{/capture}
    {assign var="browseType" value="Scheduled and Sent"}
  {/if}
  <div class="status messages no-popup">
    <table class="form-layout">
      <tr><div class="icon inform-icon"></div>
        {ts 1=$componentName}No %1 match your search criteria. Suggestions:{/ts}
      </tr>
      <tr>
        <div class="spacer"></div>
        <ul>
          <li>{ts}Check your spelling.{/ts}</li>
          <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
          <li>{ts}Select different mailing statuses.{/ts}</li>
        </ul>
      </tr>
    </table>
  </div>
{/if}
</div> {*NYSS*}

{*NYSS 12029*}
{literal}
<script type="text/javascript">
  function mailingActionTask(link, action) {
    var msg = {/literal}'{ts escape="js"}Are you sure you want to delete this mailing?{/ts}'{literal},
      title = {/literal}'{ts escape="js"}Delete Mailing{/ts}'{literal};
    if (action == 'archive') {
      msg = {/literal}'{ts escape="js"}Are you sure you want to archive this mailing?{/ts}'{literal},
      title = {/literal}'{ts escape="js"}Archive Mailing{/ts}'{literal};
    }
    else if (action ==  'cancel') {
      msg = {/literal}'{ts escape="js"}Are you sure you want to cancel this mailing?{/ts}'{literal},
      title = {/literal}'{ts escape="js"}Cancel Mailing{/ts}'{literal};
    }
    CRM.$('#actionDialog').dialog({
      title: title,
      modal: true,
      open:function() {
        CRM.$('#actionDialog').show().html(msg);
      },
      buttons: {
        {/literal}"{ts escape='js'}No{/ts}"{literal}: function() {
          CRM.$(this).dialog("close");
        },
        {/literal}"{ts escape='js'}Yes{/ts}"{literal}: function() {
          CRM.$(this).dialog("close");
          window.location.href = link;
          return;
        }
      }
    });
  }
</script>
{/literal}
