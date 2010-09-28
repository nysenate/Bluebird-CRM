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
{if $preview_type eq 'group'}
    {capture assign=infoMessage}{ts}Preview of the price set as it will be displayed within an edit form.{/ts}{/capture}
{else}
    {capture assign=infoMessage}{ts}Preview of this field as it will be displayed in an edit form.{/ts}{/capture}
{/if}
{include file="CRM/common/info.tpl"}
<div class="crm-block crm-form-block crm-price-set-preview-block">
{strip}

{foreach from=$groupTree item=cd_edit key=group_id}
    <fieldset>{if $preview_type eq 'group'}<legend>{$setTitle}</legend>{/if}
    {if $cd_edit.help_pre}<div class="messages help">{$cd_edit.help_pre}</div><br />{/if}
    <table class="form-layout">
    {foreach from=$cd_edit.fields item=element key=field_id}
    {if ($element.html_type eq 'CheckBox' || $element.html_type eq 'Radio') && $element.options_per_line }
        {assign var="name" value=`$element.name`} 
        {assign var="element_name" value=price_$field_id}
        <tr class="crm-price-field-{$name}">
           <td class="label">{$form.$element_name.label} </td>
        <td>
            {assign var="count" value="1"}
                <table class="form-layout-compressed">
                    <tr>
                   {* sort by fails for option per line. Added a variable to iterate through the element array*}
                   {assign var="index" value="1"}
                   {foreach name=outer key=key item=item from=$form.$element_name}
                        {if $index < 10}
                            {assign var="index" value=`$index+1`}
                        {else}
                          <td class="labels font-light">{$form.$element_name.$key.html}</td>
                             {if $count == $element.options_per_line}
                                {assign var="count" value="1"}
                    </tr>
                    <tr>
                            {else}
                                {assign var="count" value=`$count+1`}
                            {/if}
                         {/if}
                    {/foreach}                    
                    </tr>
            </table>
        </td>
        {if $element.help_post}
            <tr>
               <td>&nbsp;</td>
               <td class="description">{$element.help_post}</td>
            </tr>
        {/if}
    {else}
        {assign var="name" value=`$element.name`} 
        {assign var="element_name" value="price_"|cat:$field_id}  
        <tr class="crm-price-field-{$name|escape}">
           <td class="label">{$form.$element_name.label}</td>
           <td>&nbsp;{$form.$element_name.html}</td>
        </tr>		
        {if $element.help_post}
        <tr class="crm-price-set-help_post">
           <td>&nbsp;</td>
           <td class="description">{$element.help_post}</td>
        </tr>
        {/if}
	{/if}
    {/foreach}
    </table>
    {if $cd_edit.help_post}<br /><div class="messages help">{$cd_edit.help_post}</div>{/if}
    </fieldset>
{/foreach}
{/strip}
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
