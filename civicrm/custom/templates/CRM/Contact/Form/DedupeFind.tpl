{*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
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
<div class="help">
    {*NYSS*}
    {ts}You can search all contacts for duplicates or limit the search to a specific group. After initiating the rule, please be patient as it may take some time to fully process.{/ts} 
</div>
<div class="crm-block crm-form-block crm-dedupe-find-form-block">
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
   <table class="form-layout-compressed">
     <tr class="crm-dedupe-find-form-block-group_id">
       <td class="label">{$form.group_id.label}</td>
       <td>{$form.group_id.html}</td>
     </tr>
       <tr class="crm-dedupe-find-form-block-limit">
        <td class="label">{$form.limit.label}</td>
        <td>{$form.limit.html}</td>
       </tr>
     <tr>
        <td></td>
        <td class="help">You may use contacts from previous imports as a dedupe group.</td>
     </tr>
     <tr>
        <td class="label">{$form.import_group_id.label}</td>
        <td>{$form.import_group_id.html}</td>
     </tr>
   </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
