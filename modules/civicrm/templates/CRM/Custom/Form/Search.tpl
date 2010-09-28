{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{if $groupTree}
{foreach from=$groupTree item=cd_edit key=group_id}
{if $showHideLinks or $form.formName eq 'Advanced'}
  <div id="{$cd_edit.name}_show" class="section-hidden section-hidden-border">
    <a href="#" onclick="hide('{$cd_edit.name}_show'); show('{$cd_edit.name}'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}" /></a><label>{$cd_edit.title}</label><br />
  </div>
{/if}

  <div id="{$cd_edit.name}" class="form-item">
  <fieldset id="{$cd_edit.extends_entity_column_value}"><legend>
{if $showHideLinks or $form.formName eq 'Advanced'}
<a href="#" onclick="hide('{$cd_edit.name}'); show('{$cd_edit.name}_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}" /></a>
{/if}
{$cd_edit.title}</legend>
    <table class="form-layout-compressed">
    {foreach from=$cd_edit.fields item=element key=field_id}
      {assign var="element_name" value='custom_'|cat:$field_id}
      {if $element.options_per_line != 0}
         <tr>
           <td class="label">{$form.$element_name.label}</td>
           <td>
            {assign var="count" value="1"}
            {strip}
            <table class="form-layout-compressed">
             <tr>
                {* sort by fails for option per line. Added a variable to iterate through the element array*}
                {assign var="index" value="1"}
                {foreach name=outer key=key item=item from=$form.$element_name}
                {if $index < 10} {* Hack to skip QF field properties that are not checkbox elements. *}
                    {assign var="index" value=`$index+1`}
                {else}
                    {if $element.html_type EQ 'CheckBox' AND  $smarty.foreach.outer.last EQ 1} {* Put 'match ANY / match ALL' checkbox in separate row. *}
                        </tr>
                        <tr>
                        <td class="op-checkbox" colspan="{$element.options_per_line}" style="padding-top: 0px;">{$form.$element_name.$key.html}</td>
                    {else}
                        <td class="labels font-light">{$form.$element_name.$key.html}</td>
                        {if $count EQ $element.options_per_line}
                          </tr>
                          <tr>
                          {assign var="count" value="1"}
                        {else}
                          {assign var="count" value=`$count+1`}
                        {/if}
                    {/if}
                {/if}
                {/foreach}
             </tr>
            {if $element.html_type eq 'Radio'}
                <tr style="line-height: .75em; margin-top: 1px;">
                    <td> <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$element_name}', '{$form.formName}'); return false;">{ts}clear{/ts}</a>)</span></td>
                </tr>
            {/if}
            </table>
            {/strip}
           </td>
         </tr>
        {else}
            {assign var="type" value=`$element.html_type`}
            {assign var="element_name" value='custom_'|cat:$field_id}
            {if $element.is_search_range}
                {assign var="element_name_from" value=$element_name|cat:"_from"}
                {assign var="element_name_to" value=$element_name|cat:"_to"}
                <tr>
                {if $element.data_type neq 'Date'}
                    <td class="label">{$form.$element_name_from.label}</td><td>
                    {$form.$element_name_from.html|crmReplace:class:six}
                    &nbsp;&nbsp;{$form.$element_name_to.label}&nbsp;&nbsp;{$form.$element_name_to.html|crmReplace:class:six}
                {elseif $element.skip_calendar NEQ true }
                    <td class="label">{$form.$element_name_from.label}</td><td>
                    {include file="CRM/common/jcalendar.tpl" elementName=$element_name_from}
                    &nbsp;&nbsp;{$form.$element_name_to.label}&nbsp;&nbsp;
                    {include file="CRM/common/jcalendar.tpl" elementName=$element_name_to}
                {/if}  
            {else}
                <td class="label">{$form.$element_name.label}</td><td>
                {if $element.data_type neq 'Date'}
                    {$form.$element_name.html}
                {elseif $element.skip_calendar NEQ true }
                    {include file="CRM/common/jcalendar.tpl" elementName=$element_name}    
                {/if}
            {/if}
            {if $element.html_type eq 'Radio'}
                &nbsp; <a href="#" title="unselect" onclick="unselectRadio('{$element_name}', '{$form.formName}'); return false;">{ts}unselect{/ts}</a>
            {elseif $element.html_type eq 'Autocomplete-Select'}
                {include file="CRM/Custom/Form/AutoComplete.tpl"}
            {/if}
            </td>
          </tr>
	    {/if}
	    {/foreach}
	   </table>
	 </fieldset>
    </div>
 
{if  $form.formName eq 'Advanced'}
<script type="text/javascript">
{if $cd_edit.collapse_adv_display eq 0}
	hide("{$cd_edit.name}_show"); show("{$cd_edit.name}");
{else}
	show("{$cd_edit.name}_show"); hide("{$cd_edit.name}");
{/if}
</script>
{/if}
{/foreach}
{/if}

