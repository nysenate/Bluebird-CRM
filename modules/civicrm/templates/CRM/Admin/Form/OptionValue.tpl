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
{* this template is used for adding/editing/deleting activity type  *}
<div class="crm-block crm-form-block crm-admin-optionvalue-form-block">
<fieldset><legend>{if $action eq 1}{ts}New Option Value{/ts}{elseif $action eq 2}{ts}Edit Option Value{/ts}{else}{ts}Delete Option Value{/ts}{/if}</legend>
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div> 
   {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>
          {ts}WARNING: Deleting this option value will result in the loss of all records which use the option value.{/ts} {ts}This may mean the loss of a substantial amount of data, and the action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
     {else}    
      <table class="form-layout-compressed">
        <tr class="crm-admin-optionvalue-form-block-label">
            <td class="label">{$form.label.label} 
              {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_option_value' field='label' id=$id}{/if}</td>
            <td>{$form.label.html}</td>
        </tr>    
        <tr class="crm-admin-optionvalue-form-block-value">
            <td class="label">{$form.value.label}</td>
            <td>{$form.value.html}<br /> {if $config->languageLimit|@count >= 2}
            <span class="description">{ts}The same option value is stored for all languages. Changing this value will change it for all languages.{/ts}</span> {/if} </td>
        </tr> 
        <tr class="crm-admin-optionvalue-form-block-name">   
            <td class="label">{$form.name.label}</td>
            <td>{$form.name.html}</td>
        </tr>
        <tr class="crm-admin-optionvalue-form-block-grouping">
            <td class="label">{$form.grouping.label}</td>
            <td>{$form.grouping.html}</td>
        </tr>
        <tr class="crm-admin-optionvalue-form-block-description">
            <td class="label">{$form.description.label}</td>
            <td>{$form.description.html}</td>
        </tr>
        <tr class="crm-admin-optionvalue-form-block-weight">
            <td class="label">{$form.weight.label}</td>
            <td>{$form.weight.html}</td>
        </tr>
        {if $form.is_default}
        <tr class="crm-admin-optionvalue-form-block-is_default">
            <td class="label">{$form.is_default.label}</td>
            <td>{$form.is_default.html}</td>
        </tr>
        {/if}
        <tr class="crm-admin-optionvalue-form-block-is_active">
            <td class="label">{$form.is_active.label}</td>
            <td>{$form.is_active.html}</td>
        </tr>
        <tr class="crm-admin-optionvalue-form-block-is_optgroup">
            <td class="label">{$form.is_optgroup.label}</td>
            <td>{$form.is_optgroup.html}</td>
        </tr>
       {if $form.contactOptions}{* contactOptions is exposed for email/postal greeting and addressee types to set filter for contact types *}
        <tr class="crm-admin-optionvalue-form-block-contactOptions">
            <td class="label">{$form.contactOptions.label}</td>
            <td>{$form.contactOptions.html}</td>
        </tr>
       {/if}  
      </table> 
     {/if}
         <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>      
</fieldset>
</div>
