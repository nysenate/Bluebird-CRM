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

<div class="crm-block crm-form-block crm-dedupe-rules-form-block">
  <h2>{ts 1=$contact_type}Matching Rules for %1 Contacts{/ts}</h2>
    <div id="help">
        {ts}Configure up to five fields to evaluate when searching for 'suspected' duplicate contact records.{/ts} {help id="id-rules"}
    </div>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <table class="form-layout-compressed">
     <tr class="crm-dedupe-rules-form-block-label">
        <td class="label">{$form.name.label}</td>
        <td>{$form.name.html}</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-level">
        <td class="label">{$form.level.label}</td>
        <td>{$form.level.html}</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-is_default">
        <td class="label">{$form.is_default.label}</td>
        <td>{$form.is_default.html}</td>
     </tr>
  </table>
  <table style="width: auto;">
     <tr class="columnheader"><th>{ts}Field{/ts}</th><th>{ts}Length{/ts}</th><th>{ts}Weight{/ts}</th></tr>
         {section name=count loop=5}
         {capture assign=where}where_{$smarty.section.count.index}{/capture}
         {capture assign=length}length_{$smarty.section.count.index}{/capture}
         {capture assign=weight}weight_{$smarty.section.count.index}{/capture}
     <tr class="{cycle values="odd-row,even-row"}">
          <td>{$form.$where.html}</td>
          <td>{$form.$length.html}</td>
          <td>{$form.$weight.html}</td>
     </tr>
    {/section}
    <tr class="columnheader"><th colspan="2" style="text-align: right;">{$form.threshold.label}</th>
        <td>{$form.threshold.html}</td>
    </tr>
 </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
