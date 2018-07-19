{assign var='locationIndex' value=1}
{if $address}
  {foreach from=$address item=add key=locationIndex}
    <div class="crm-address_{$locationIndex} crm-address-block crm-summary-block">
      {include file="CRM/Contact/Page/Inline/Address.tpl"}
    </div>
  {/foreach}
  {assign var='locationIndex' value=$locationIndex+1}
{/if}
{* add new link *}
{if $permission EQ 'edit'}
  {assign var='add' value=0}
  <div class="crm-address-block crm-summary-block">
    {include file="CRM/Contact/Page/Inline/Address.tpl"}
  </div>
{/if}