{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{* CiviMember DashBoard (launch page) *}
<h3>{ts}Membership Summary{/ts} {help id="id-member-intro"}</h3>
<table class="report">
    <tr class="columnheader-dark">
        <th scope="col">{ts}Members by Type{/ts}</th>
        {if $preMonth}
            <th scope="col">{$premonth} &ndash; {ts}New/Renew (Last Month){/ts}</th>
        {/if}
        <th scope="col">{$month} &ndash; {if $isCurrent}{ts}New/Renew (MTD){/ts}{else}{ts}New/Renew{/ts}{/if}</th>
        <th scope="col">
            {if $year eq $currentYear}
                {$year} &ndash; {ts}New/Renew (YTD){/ts}
            {else}
                {$year} &ndash; {ts 1=$month}New/Renew through %1{/ts}
            {/if}
        </th>
        <th scope="col">
            {if $isCurrent}
                {ts}Current #{/ts}
            {else}
                {ts 1=$month 2=$year}Members as of %1 %2{/ts}
            {/if}
        </th>
    </tr>

    {foreach from=$membershipSummary item=row}
        <tr>
            <td><strong>{$row.month.name}</strong></td>
            {if $preMonth}
                <td class="label"><a href="{$row.premonth.url}" title="view details">{$row.premonth.count}</a></td> {* member/search?reset=1&force=1&membership_type_id=1&current=1&start=20060601000000&end=20060612174244 *}
            {/if}
            <td class="label"><a href="{$row.month.url}" title="view details">{$row.month.count}</a></td> {* member/search?reset=1&force=1&membership_type_id=1&current=1&start=20060601000000&end=20060612174244 *}
            <td class="label"><a href="{$row.year.url}" title="view details">{$row.year.count}</a></td> {* member/search?reset=1&force=1&membership_type_id=1&current=1&start=20060101000000&end=20060612174244 *} 
            <td class="label">
                {if $isCurrent}
                    <a href="{$row.current.url}" title="view details">{$row.current.count}</a>
                {else}
                    <a href="{$row.total.url}" title="view details">{$row.total.count}
                {/if}
            </td> {* member/search?reset=1&force=1&membership_type_id=1&current=1 *}  
        </tr>
    {/foreach}

    <tr class="columnfooter">
        <td><strong>{ts}Totals (all types){/ts}</strong></td>
        {if $preMonth}
            <td class="label"><a href="{$totalCount.premonth.url}" title="view details">{$totalCount.premonth.count}</a></td> {* member/search?reset=1&force=1&current=1&start=20060601000000&end=20060612174244 *}
        {/if}
        <td class="label"><a href="{$totalCount.month.url}" title="view details">{$totalCount.month.count}</a></td> {* member/search?reset=1&force=1&current=1&start=20060601000000&end=20060612174244 *}
        <td class="label"><a href="{$totalCount.year.url}" title="view details">{$totalCount.year.count}</a></td> {* member/search?reset=1&force=1&current=1&start=20060101000000&end=20060612174244 *}
        <td class="label">
            {if $isCurrent}
                <a href="{$row.total.url}" title="view details">{$totalCount.current.count}</a>
            {else}
                <a href="{$totalCount.total.url}" title="view details">{$totalCount.total.count}</a>
            {/if}
        </td> {* member/search?reset=1&force=1&current=1 *}
    </tr>
</table>

<div class="spacer"></div>

{if $rows}
{* if $pager->_totalItems *}
    <h3>{ts}Recent Memberships{/ts}</h3>
    <div class="form-item">
        { include file="CRM/Member/Form/Selector.tpl" context="dashboard" }
    </div>
{* /if *}
{/if}
