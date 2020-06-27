{assign var=paneTableName    value=$filterPaneGroups.$filterPane.table_name}
{assign var=groupExtendsContact   value=$filterPaneGroups.$filterPane.group_extends_contact}
{if $groupExtendsContact}
  {foreach from=$filtersGroupedByTableSets.$paneTableName item=table key=tableKey}
    {include file="CRM/Report/Form/Tabs/FilterCriteria.tpl"}
    <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}">
      <td colspan=2 class=""><h5>{$filterExtendsContactGroup.$tableKey.group_field_label}</h5></td>
    </tr>
    {include file="CRM/Report/Form/Tabs/FilterField.tpl" isGroupedByTableSet='YES'}
  {/foreach}
{else}
  {assign var=table value=$filters.$paneTableName}
  {include file="CRM/Report/Form/Tabs/FilterCriteria.tpl"}
  {include file="CRM/Report/Form/Tabs/FilterField.tpl" isGroupedByTableSet='NO'}
{/if}
