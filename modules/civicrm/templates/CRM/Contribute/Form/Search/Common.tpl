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
<tr>
	<td>{$form.contribution_date_low.label} <br />
	{include file="CRM/common/jcalendar.tpl" elementName=contribution_date_low}</td>

	<td>{$form.contribution_date_high.label}<br />
	{include file="CRM/common/jcalendar.tpl" elementName=contribution_date_high}</td>
</tr>
<tr>
	<td><label>{ts}Contribution Amounts{/ts}</label> <br />
	{$form.contribution_amount_low.label}
	{$form.contribution_amount_low.html} &nbsp;&nbsp;
	{$form.contribution_amount_high.label}
	{$form.contribution_amount_high.html} </td>
	<td><label>{ts}Contribution Status{/ts}</label> <br />
	{$form.contribution_status_id.html} </td>
</tr>
<tr>
	<td><label>{ts}Paid By{/ts}</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{$form.contribution_check_number.label} <br />
	{$form.contribution_payment_instrument_id.html}&nbsp;&nbsp;&nbsp;&nbsp;
	{$form.contribution_check_number.html}
	</td>
	<td>{$form.contribution_transaction_id.label} <br />
	{$form.contribution_transaction_id.html}</td>
</tr>
<tr>
	<td>
	{$form.contribution_receipt_date_isnull.html}&nbsp;{$form.contribution_receipt_date_isnull.label}<br />
	{$form.contribution_thankyou_date_isnull.html}&nbsp;{$form.contribution_thankyou_date_isnull.label}
	</td>
	<td>
	{$form.contribution_pay_later.html}&nbsp;{$form.contribution_pay_later.label}<br />
	{$form.contribution_recurring.html}&nbsp;{$form.contribution_recurring.label}<br />
	{$form.contribution_test.html}&nbsp;{$form.contribution_test.label}</td>
</tr>
<tr>
	<td><label>{ts}Contribution Type{/ts}</label> <br />
	{$form.contribution_type_id.html|crmReplace:class:twenty}</td>
	<td><label>{ts}Contribution Page{/ts}</label> <br />
	{$form.contribution_page_id.html|crmReplace:class:twenty}</td>
</tr>
<tr>
	<td>{$form.contribution_in_honor_of.label} <br />
	{$form.contribution_in_honor_of.html|crmReplace:class:twenty}</td>
	<td>{$form.contribution_source.label} <br />
	{$form.contribution_source.html|crmReplace:class:twenty}</td>
</tr>
<tr>
	<td>{$form.contribution_pcp_made_through_id.label} <br />
	{$form.contribution_pcp_made_through_id.html|crmReplace:class:twenty}</td>
	<td>{$form.contribution_pcp_display_in_roll.label}
	{$form.contribution_pcp_display_in_roll.html}<span class="crm-clear-link">(<a href="javascript:unselectRadio('contribution_pcp_display_in_roll','{$form.formName}')">{ts}clear{/ts}</a>)</span></td>
</tr>

<tr>
	<td><label>{ts}Currency{/ts}</label> <br />
	{$form.contribution_currency_type.html}</td>
</tr>
{if $contributeGroupTree}
<tr>
	<td colspan="2">
	{include file="CRM/Custom/Form/Search.tpl" groupTree=$contributeGroupTree showHideLinks=false}</td>
</tr>
{/if}
