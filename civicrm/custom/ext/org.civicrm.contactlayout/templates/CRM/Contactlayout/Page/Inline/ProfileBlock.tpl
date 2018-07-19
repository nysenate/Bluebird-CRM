<div {if $permission EQ 'edit'} class="crm-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "gid": {$block.profile_id}, "class_name": "CRM_Contactlayout_Form_Inline_ProfileBlock"{rdelim}' {/if}>
  <div class="crm-clear crm-inline-block-content" {if $permission EQ 'edit'}title="{ts}Edit{/ts}"{/if}>
    {if $permission EQ 'edit'}
      <div class="crm-edit-help">
        <span class="crm-i fa-pencil"></span> {ts}Edit{/ts}
      </div>
    {/if}
    {foreach from=$profileBlock item='profileRow'}
      <div class="crm-summary-row">
        <div class="crm-label">{$profileRow.label|escape}</div>
        <div class="crm-content">{$profileRow.value|escape}</div>
      </div>
    {/foreach}
  </div>
</div>
