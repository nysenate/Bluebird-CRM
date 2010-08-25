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
{* Displays contribution/event fees when price set is used. *}
{foreach from=$lineItem item=value key=priceset}
    {if $value neq 'skip'}
    {if $lineItem|@count GT 1} {* Header for multi participant registration cases. *}
        {if $priceset GT 0}<br />{/if}
        <strong>{ts}Participant {$priceset+1}{/ts}</strong> {$part.$priceset.info}
    {/if}				 
    <table>
            <tr class="columnheader">
                <th>{ts}Item{/ts}</th>
                <th class="right">{ts}Qty{/ts}</th>
                <th class="right">{ts}Unit Price{/ts}</th>
                <th class="right">{ts}Total Price{/ts}</th>
	 {if $participantCount }<th class="right">{ts}Total Participants{/ts}</th>{/if} 
            </tr>
                {foreach from=$value item=line}
            <tr>
                <td>{$line.description}</td>
                <td class="right">{$line.qty}</td>
                <td class="right">{$line.unit_price|crmMoney}</td>
                <td class="right">{$line.line_total|crmMoney}</td>
         {if $participantCount }<td class="right">{$line.participant_count}</td> {/if}
            </tr>
            {/foreach}
    </table>
    {/if}
{/foreach}

<div class="crm-section no-label total_amount-section">
    <div class="content bold">
        {if $context EQ "Contribution"}
            {ts}Contribution Total{/ts}:
        {elseif $context EQ "Event"}
            {ts}Event Total{/ts}: 
        {/if}
    {$totalAmount|crmMoney}
    </div>
    <div class="content bold">
      {if $participantCount}
      {ts}Total Participants{/ts}:
      {foreach from=$lineItem item=pcount}
      {foreach from=$pcount item=p_count}
      {assign var="totalcount" value=$totalcount+$p_count.participant_count}
      {/foreach}
      {/foreach}
      {$totalcount}
      {/if}
     </div>    
</div>

{if $hookDiscount.message}
    <div class="crm-section hookDiscount-section">
        <em>({$hookDiscount.message})</em>
    </div>
{/if}
