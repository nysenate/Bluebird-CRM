{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}

{foreach from=$groupTree item=cd_edit key=group_id}
{*NYSS remove org and contact fields as they are integrated directly; collapse attachments by default*}
{if $group_id neq 3 && $group_id neq 1 && $group_id neq 8}
  {if $cd_edit.is_multiple eq 1}
    {assign var=tableID value=$cd_edit.table_id}
    {assign var=divName value=$group_id|cat:"_$tableID"}
    <div></div>
    <div
     class="crm-accordion-wrapper crm-custom-accordion {if $cd_edit.collapse_display and !$skipTitle}collapsed{/if}">
  {else}
    <div id="{$cd_edit.name}"
       class="crm-accordion-wrapper crm-custom-accordion {if $cd_edit.collapse_display}collapsed{/if}">
  {/if}
    <div class="crm-accordion-header">
      {$cd_edit.title}
    </div>
    <div id="customData{$group_id}" class="crm-accordion-body">
      {include file="CRM/Custom/Form/Edit/CustomData.tpl" customDataEntity=''}
      {include file="CRM/Form/attachmentjs.tpl"}
    </div>
    <!-- crm-accordion-body-->
  </div>
  <!-- crm-accordion-wrapper -->
  <div id="custom_group_{$group_id}_{$cgCount}"></div>
  {/if}{*NYSS*}
  {/foreach}

  {include file="CRM/common/customData.tpl"}

  {include file="CRM/Form/attachmentjs.tpl"}
