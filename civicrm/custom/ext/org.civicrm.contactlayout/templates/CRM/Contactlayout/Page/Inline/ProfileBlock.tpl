<div id="{$block.selector|replace:'#':''}" {if $permission EQ 'edit'} class="crm-inline-edit" data-dependent-fields={$block.refresh|@json_encode} data-edit-params='{ldelim}"cid": "{$contactId}", "gid": {$block.profile_id}, "class_name": "CRM_Contactlayout_Form_Inline_ProfileBlock"{rdelim}' {/if}>
  <div class="crm-clear crm-inline-block-content" {if $permission EQ 'edit'}title="{ts}Edit{/ts}"{/if}>
    {if $permission EQ 'edit'}
      <div class="crm-edit-help">
        <span class="crm-i fa-pencil"></span> {ts}Edit{/ts}
      </div>
    {/if}
    {foreach from=$profileBlock item='profileRow'}
      <div class="crm-summary-row profile-block-{$profileRow.name}">
        <div class="crm-label">{$profileRow.label|escape}</div>
        <div class="crm-content">
          {if $profileRow.name == 'tag'}
            {foreach from=$contactTag item=tagName key=tagId}
              <span class="crm-tag-item" {if !empty($allTags.$tagId.color)}style="background-color: {$allTags.$tagId.color}; color: {$allTags.$tagId.color|colorContrast};"{/if} title="{$allTags.$tagId.description|escape}">
                {$tagName}
              </span>
            {/foreach}
          {elseif $profileRow.name == 'image_URL' && !empty($imageURL)}
            <div id="crm-contact-thumbnail">
              {include file="CRM/Contact/Page/ContactImage.tpl"}
            </div>
          {else}
            {$profileRow.value|purify}
          {/if}
        </div>
      </div>
    {/foreach}
  </div>
</div>
