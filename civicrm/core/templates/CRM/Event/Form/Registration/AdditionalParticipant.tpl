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
{if $skipCount}
    <h3>Skipped Participant(s): {$skipCount}</h3>
{/if}
{if $action & 1024}
    {include file="CRM/Event/Form/Registration/PreviewHeader.tpl"}
{/if}

{include file="CRM/common/TrackingFields.tpl"}

{capture assign='reqMark'}<span class="marker"  title="{ts}This field is required.{/ts}">*</span>{/capture}

{*CRM-4320*}
{if $statusMessage}
    <div class="messages status">
        <p>{$statusMessage}</p>
    </div>
{/if}

<div class="crm-block crm-event-additionalparticipant-form-block">
{if $priceSet}
    <fieldset id="priceset"><legend>{$event.fee_label}</legend>
    <table class="form-layout">
    {foreach from=$priceSet.fields item=element key=field_id}
        {if ($element.html_type eq 'CheckBox' || $element.html_type == 'Radio') && $element.options_per_line}
            {assign var="element_name" value=price_$field_id}
         <tr class="crm-event-additionalparticipant-form-block-element_name">
            <td class="label">{$form.$element_name.label}</td>
            <td>
            {assign var="count" value="1"}
            <table class="form-layout-compressed">
                <tr class="crm-event-additionalparticipant-form-block-element_name">
                    {foreach name=outer key=key item=item from=$form.$element_name}
                        {if is_numeric($key) }
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
         </tr>
        {else}
            {assign var="name" value=`$element.name`}
            {assign var="element_name" value="price_"|cat:$field_id}
            <td class="label">{$form.$element_name.label}</td>
            <td>&nbsp;{$form.$element_name.html}</td>
        </tr>
        {/if}
        {if $element.help_post}
        <tr>
           <td>&nbsp;</td>
           <td class="description">{$element.help_post}</td>
        </tr>
        {/if}
    {/foreach}
    </table>
    <div>
     <table class="form-layout">
         <tr></tr>
         <tr>
            <td>{include file="CRM/Price/Form/Calculate.tpl"}</td>
         </tr>
     </table>
    </div> 
    </fieldset>
{else}
    {if $paidEvent}
        <table class="form-layout-compressed">
            <tr class="crm-event-additionalparticipant-form-block-amount">
                <td class="label nowrap">{$event.fee_label} <span class="marker">*</span></td>
                <td>&nbsp;</td>
                <td>{$form.amount.html}</td>
            </tr>
        </table>
    {/if}
{/if}

{assign var=n value=email-$bltID}
<table class="form-layout-compressed">
    <tr>
        <td class="label nowrap">{$form.$n.label}</td><td>{$form.$n.html}</td>
    </tr>
</table>

{include file="CRM/UF/Form/Block.tpl" fields=$additionalCustomPre} 
{include file="CRM/UF/Form/Block.tpl" fields=$additionalCustomPost} 

<div id="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
</div>
</div>
