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
{* this template is used for editing Site Preferences  *}
<div class="crm-block crm-form-block crm-preferences-display-form-block"> 
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
     <table class="form-layout">
        {if $form.contact_view_options.html}
	    <tr class="crm-preferences-display-form-block-contact_view_options">
               <td class="label">{$form.contact_view_options.label}</td>
               <td>{$form.contact_view_options.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Select the <strong>tabs</strong> that should be displayed when viewing a contact record. EXAMPLE: If your organization does not keep track of 'Relationships', then un-check this option to simplify the screen display. Tabs for Contributions, Pledges, Memberships, Events, Grants and Cases are also hidden if the corresponding component is not enabled.{/ts} {docURL page="Enable Components"}</td>
            </tr>
	{/if}
	{if $form.contact_edit_options.html}        		       
	        <tr class="crm-preferences-display-form-block-contact_edit_options">
               <td class="label">{$form.contact_edit_options.label}</td>
               <td>{$form.contact_edit_options.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Select the sections that should be included when adding or editing a contact record. EXAMPLE: If your organization does not record Gender and Birth Date for individuals, then simplify the form by un-checking this option.{/ts}</td>
            </tr>
	{/if}
	{if $form.advanced_search_options.html}
            <tr class="crm-preferences-display-form-block-advanced_search_options">
               <td class="label">{$form.advanced_search_options.label}</td>
               <td>{$form.advanced_search_options.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Select the sections that should be included in the Basic and Advanced Search forms. EXAMPLE: If you don't track Relationships - then you do not need this section included in the advanced search form. Simplify the form by un-checking this option.{/ts}
               </td>
            </tr>
	{/if}
	{if $form.user_dashboard_options.html}
            <tr class="crm-preferences-display-form-block-user_dashboard_options">
               <td class="label">{$form.user_dashboard_options.label}</td>
               <td>{$form.user_dashboard_options.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Select the sections that should be included in the Contact Dashboard. EXAMPLE: If you don't want constituents to view their own contribution history, un-check that option.{/ts}
               </td>
            </tr>
	{/if}
	{if $form.wysiwyg_editor.html}
            <tr class="crm-preferences-display-form-block-wysiwyg_editor">
               <td class="label">{$form.wysiwyg_editor.label}</td>
               <td>{$form.wysiwyg_editor.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Select the HTML WYSIWYG Editor provided for fields that allow HTML formatting. Select 'Textarea' if you don't want to provide a WYSIWYG Editor (users will type text and / or HTML code into plain text fields).{/ts}
               </td>
            </tr>
	{/if}
	{if $form.display_name_format.html}
            <tr class="crm-preferences-display-form-block-display_name_format" >
               <td class="label">{$form.display_name_format.label}</td>
               <td>{$form.display_name_format.html}</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Display name format for individual contact display names.{/ts}</td>
            </tr>
	{/if}
	{if $form.sort_name_format.html}
            <tr class="crm-preferences-display-form-block-sort_name_format">
               <td class="label">{$form.sort_name_format.label}</td>
               <td>{$form.sort_name_format.html}</td>
            </tr>
            <tr  class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description">{ts}Sort name format for individual contact display names.{/ts}</td>
            </tr>
          </table>
	{/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
   </div>
