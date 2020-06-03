{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
<div class="crm-form-block crm-block crm-contact-task-addtotag-form-block">
<h3>
{ts}Tag Contact(s){/ts}
</h3>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<table class="form-layout-compressed">
    <tr class="crm-contact-task-addtotag-form-block-tag">
        {*NYSS 13011*}
        <td class="label"><label>Issue Codes</label></td>
        <td>
            <div class="listing-box">
            {foreach from=$form.tag item="tag_val" key=k}{*NYSS add id*}
                {if $k != 291}{*NYSS 13011*}
                    <div class="{cycle values="odd-row,even-row"}" id="crm-tagRow_tag{$k}">
                    {$tag_val.html}
                    </div>
                {/if}
            {/foreach}
            </div>
        </td>
    </tr>
    <tr>
        {*NYSS 13011*}
        {include file="CRM/common/Tagset.tpl" tableLayout=1}
    </tr>

    <tr><td>{include file="CRM/Contact/Form/Task.tpl"}</td></tr>
</table>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
