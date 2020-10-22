<div id="report-tab-set-relationships" class="civireport-criteria">
  <p>{ts}Filters defined on this tab restrict what data is brought in for that join. For example if you filter by website type it
    will restrict the websites in the website column but contacts with that type will not be filtered out.{/ts}</p>
  <table class="report-layout">
    {assign var="counter" value=1}
    {foreach from=$join_filters item=table key=tableName}
    {assign  var="filterCount" value=$table|@count}
    {* Wrap custom field sets in collapsed accordion pane. *}
    {if $filterGroups.$tableName.group_title and $filterCount gte 1}
    {* we should close table that contains other filter elements before we start building custom group accordian
     *}
    {if $counter eq 1}
  </table>
  {assign var="counter" value=0}
  {/if}
  <div class="crm-accordion-wrapper crm-accordion collapsed">
    <div class="crm-accordion-header">
      {$filterGroups.$tableName.group_title}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <table class="report-layout">
        {/if}
        {foreach from=$table item=field key=fieldName}
          {assign var=fieldOp     value=$fieldName|cat:"_op"}
          {assign var=filterVal   value=$fieldName|cat:"_value"}
          {assign var=filterMin   value=$fieldName|cat:"_min"}
          {assign var=filterMax   value=$fieldName|cat:"_max"}
          {if $field.operatorType & 4}
            <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}">
              <td class="label report-contents">{$field.title}</td>
              {include file="CRM/Core/DateRange.tpl" fieldName=$fieldName from='_from' to='_to'}
            </tr>
            {* custom override for handling of single date concept - ie. a dividing date *}
          {elseif $field.operatorType eq 3}
            <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}" {if $field.no_display} style="display: none;"{/if}>
              <td class="label report-contents">{$field.title}</td>
              <td class="report-contents">{$form.$fieldOp.html}</td>
              <td>
                <span id="{$filterVal}_cell">{include file="CRM/common/jcalendar.tpl" elementName=$filterVal}</span>
                      <span id="{$filterMin}_max_cell">{$form.$filterMin.label}&nbsp;{$form.$filterMin.html}
                        &nbsp;&nbsp;{$form.$filterMax.label}&nbsp;{$form.$filterMax.html}</span>
              </td>
            </tr>
            {* end of custom override *}
          {elseif $form.$fieldOp.html}
            <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}" {if $field.no_display} style="display: none;"{/if}>
              <td class="label report-contents">{$field.title}</td>
              <td class="report-contents">{$form.$fieldOp.html}</td>
              <td>
                <span id="{$filterVal}_cell">{$form.$filterVal.label}&nbsp;{$form.$filterVal.html}</span>
                <span id="{$filterMin}_max_cell">{$form.$filterMin.label}&nbsp;{$form.$filterMin.html}&nbsp;&nbsp;{$form.$filterMax.label}&nbsp;{$form.$filterMax.html}</span>
              </td>
            </tr>
          {/if}
        {/foreach}
        {if $filterGroups.$tableName.group_title}
      </table>
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
  {assign var=closed value="1"} {*-- ie table tags are closed-- *}
  {else}
  {assign var=closed     value="0"} {*-- ie table tags are not closed-- *}
  {/if}
  {/foreach}
  {if $closed eq 0 }
      </table>
    {/if}
</div>
