{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
     <fieldset id="priceset" class="crm-group priceset-group"><legend>{$event.fee_label}</legend>
     	 {include file="CRM/Price/Form/PriceSet.tpl"}
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
