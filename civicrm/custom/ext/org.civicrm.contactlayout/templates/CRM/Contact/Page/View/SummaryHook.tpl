{foreach from=$layoutBlocks item="row" key="rowNum"}
  <div class="contact_panel crm-contact-summary-layout-row crm-contact-summary-layout-row-{$rowNum+1} crm-contact-summary-row-{$row|@count}-col">
    {foreach from=$row item="column" key="colNum"}
      <div class="crm-contact-summary-layout-col crm-contact-summary-layout-col-{$colNum+1}">
        {foreach from=$column item='block'}
          <div class="{if $block.collapsible}crm-collapsible{if $block.collapsed} collapsed{/if}{/if}">
            {if (!empty($block.collapsible) || !empty($block.showTitle))}
              <div class="collapsible-title">
                {$block.title}
              </div>
            {/if}
            <div class="crm-summary-block">
              {include file=$block.tpl_file}
            </div>
          </div>
        {/foreach}
      </div>
    {/foreach}
  </div>
{/foreach}