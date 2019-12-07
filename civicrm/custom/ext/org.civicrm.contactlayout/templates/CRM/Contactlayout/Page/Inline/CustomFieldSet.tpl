{assign var='customGroupId' value=$block.custom_group_id}
{assign var='customValues' value=$viewCustomData.$customGroupId}
{assign var='cgcount' value=1}
{foreach from=$customValues item='cd_edit' key='customRecId'}
  {include file="CRM/Contact/Page/View/CustomDataFieldView.tpl"}
  {assign var='cgcount' value=$cgcount+1}
{/foreach}
