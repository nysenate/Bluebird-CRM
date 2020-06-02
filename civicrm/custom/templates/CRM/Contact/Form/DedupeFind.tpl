{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
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
