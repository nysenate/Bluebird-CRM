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
{strip}
{if $list}
<div class="crm-block crm-form-block crm-report-instanceList-form-block">
{counter start=0 skip=1 print=false}
{foreach from=$list item=rows key=report}		
<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion_{$report}-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  	{if $title}{$title}{elseif $report EQ 'Contribute'}{ts}Contribution Reports{/ts}{else}{$report} {ts}Reports{/ts}{/if}</a>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
	<div id="{$report}" class="boxBlock">
	    <table class="report-layout">
		{foreach from=$rows item=row}
		    <tr id="row_{counter}" class="crm-report-instanceList">
			<td class="crm-report-instanceList-title" style="width:35%"><a href="{$row.url}" title="{ts}Run this report{/ts}">&raquo; <strong>{$row.title}</strong></a></td>
			<td class="crm-report-instanceList-description">{$row.description}</td>
			{if $row.deleteUrl}
			    <td class="crm-report-instanceList-deleteUrl" style = "width:5%"><a href="{$row.deleteUrl}" onclick="return window.confirm('{ts}Are you sure you want delete this report?{/ts} {ts}This action cannot be undone.{/ts}');">{ts}Delete{/ts}</a></td>
			{/if}
		    </tr>
		{/foreach}
	    </table>
	</div>
</div>
</div>
{/foreach}
</div>

    {if $reportUrl}
    <div class="action-link">
	<a href="{$reportUrl}" class="button"><span>&raquo; {ts}View All Reports{/ts}</span></a>
    </div>
    {/if}

{else}
<div class="crm-content-block">    
<div class="messages status">
	<div class="icon inform-icon"></div>&nbsp;
        {ts}No report instances have been created for your site.{/ts} &nbsp;
        {if $templateUrl}
            {ts 1=$templateUrl}You can create reports by selecting from the <a href="%1">list of report templates here.</a>{/ts}
        {else}
            {ts}Contact your site administrator for help creating reports.{/ts}
        {/if}
    </div>
</div>
{/if}
{/strip}
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}
