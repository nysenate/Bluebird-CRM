<div class="contact_panel">
  {foreach from=$layoutBlocks item="column" key="columnNo"}
    <div class="contactCard{if $columnNo}Right{else}Left{/if}">
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