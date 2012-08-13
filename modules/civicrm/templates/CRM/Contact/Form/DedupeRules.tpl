{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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

<h3>{ts 1=$contact_type}Matching Rule for %1 Contacts{/ts}</h3>
<div class="crm-block crm-form-block crm-dedupe-rules-form-block">
    <div id="help">
        {ts}Configure up to five fields to evaluate when searching for 'suspected' duplicate contact records.{/ts} {help id="id-rules"}
    </div>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <table class="form-layout">
     <tr class="crm-dedupe-rules-form-block-title">
        <td class="label">{$form.title.label}</td>
        <td>
            {$form.title.html}
            <div class="description">
                {ts}Enter descriptive name for this matching rule.{/ts}
            </div>
        </td>
    </tr>
    <tr class="crm-dedupe-rules-form-block-level">
        <td class="label">{$form.level.label}</td>
        <td>{$form.level.html}</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-is_default">
        <td class="label">{$form.is_default.label}</td>
        <td>{$form.is_default.html}</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-is_reserved">
        <td class="label">{$form.is_reserved.label}</td>
        <td>{$form.is_reserved.html}</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-fields">
        <td></td>
        <td>
            <table class="form-layout-compressed">
                {if $isReserved}
                    <tr>
                        <td><div class="status message">{ts}Note: You cannot edit fields for a reserved rule.{/ts}</div></td>
                    </tr>
                {/if}
                <tr class="columnheader"><td>{ts}Field{/ts}</td><td>{ts}Length{/ts}</td><td>{ts}Weight{/ts}</td></tr>
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
                    <tr class="columnheader"><td colspan="2">{$form.threshold.label}</td>
                        <td>{$form.threshold.html}</td>
                    </tr>
            </table>
        </td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
