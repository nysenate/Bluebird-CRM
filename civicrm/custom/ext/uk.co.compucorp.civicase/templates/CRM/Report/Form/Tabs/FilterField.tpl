<table class="report-layout">
  {foreach from=$table item=field key=fieldName}

    {assign var=fieldOp     value=$fieldName|cat:"_op"}
    {assign var=filterVal   value=$fieldName|cat:"_value"}
    {assign var=filterMin   value=$fieldName|cat:"_min"}
    {assign var=filterMax   value=$fieldName|cat:"_max"}

    {if $isGroupedByTableSet == 'YES'}
      {assign var=fieldLabel value=$field.label}
    {else}
      {assign var=fieldLabel value=$field.title}
    {/if}

    {if $field.operatorType & 4}
      <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}">
        <td class="label report-contents">{ts}{$fieldLabel}{/ts}</td>
        {include file="CRM/Core/DateRange.tpl" fieldName=$fieldName from='_from' to='_to'}
      </tr>
    {elseif $form.$fieldOp.html}
      <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}" {if $field.no_display} style="display: none;"{/if}>
        <td class="label report-contents">{ts}{$fieldLabel}{/ts}</td>
        <td class="report-contents">{$form.$fieldOp.html}</td>
        <td>
          <span id="{$filterVal}_cell">{$form.$filterVal.label}&nbsp;{$form.$filterVal.html}</span>
          <span id="{$filterMin}_max_cell">{$form.$filterMin.label}&nbsp;{$form.$filterMin.html}&nbsp;&nbsp;{$form.$filterMax.label}&nbsp;{$form.$filterMax.html}</span>
        </td>
      </tr>
    {/if}
  {/foreach}
</table>
