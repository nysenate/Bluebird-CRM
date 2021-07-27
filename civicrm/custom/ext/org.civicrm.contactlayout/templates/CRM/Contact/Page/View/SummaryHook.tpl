{foreach from=$layoutBlocks item="row" key="rowNum"}
  <div class="contact_panel crm-contact-summary-layout-row crm-contact-summary-layout-row-{$rowNum+1} crm-contact-summary-row-{$row|@count}-col">
    {foreach from=$row item="column" key="colNum"}
      <div class="crm-contact-summary-layout-col crm-contact-summary-layout-col-{$colNum+1}">
        {foreach from=$column item='block'}
          {* $viewCustomData is assigned by CRM_Core_BAO_CustomGroup::buildCustomDataView and contains an array of applicable custom fields keyed by group id *}
          {if empty($block.custom_group_id) || !empty($viewCustomData[$block.custom_group_id])}
            <div class="{if !empty($block.collapsible)}crm-collapsible{if !empty($block.collapsed)} collapsed{/if}{/if}">
              {if (!empty($block.collapsible) || !empty($block.showTitle))}
                <div class="collapsible-title">
                  {$block.title}
                </div>
              {/if}
              <div class="crm-summary-block">
                {include file=$block.tpl_file}
              </div>
            </div>
          {/if}
        {/foreach}
      </div>
    {/foreach}
  </div>
{/foreach}
