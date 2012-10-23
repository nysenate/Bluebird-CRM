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
{if !$printOnly} {* NO print section starts *}

    {* build the print pdf buttons *}
    {if $rows}
        <div class="crm-tasks">
        {assign var=print value="_qf_"|cat:$form.formName|cat:"_submit_print"}
        {assign var=pdf   value="_qf_"|cat:$form.formName|cat:"_submit_pdf"}
        {assign var=csv   value="_qf_"|cat:$form.formName|cat:"_submit_csv"}
        {assign var=group value="_qf_"|cat:$form.formName|cat:"_submit_group"}
        {assign var=chart value="_qf_"|cat:$form.formName|cat:"_submit_chart"}
        <table style="border:0;">
            <tr>
                <td>
                    <table class="form-layout-compressed">
                        <tr>
                            <td>{$form.$csv.html}&nbsp;&nbsp;</td>
                            {*NYSS - only display if low total record count - restriction removed with 5097/reintroduced*}
                            {if $pager->_totalItems < 10000}
                            	<td>{$form.$print.html}&nbsp;&nbsp;</td>
                            	<td>{$form.$pdf.html}&nbsp;&nbsp;</td>
                            {else}
                            	<td><span><em>To print or generate a PDF for your report, please reduce the number of contacts by further restricting your selection criteria. You may use the search tools to run your search and export to a .csv file, which may be opened and manipulated in Excel.</em></span></td>
                            {/if}
                            {if $instanceUrl}
                                <td>&nbsp;&nbsp;&raquo;&nbsp;<a href="{$instanceUrl}">{ts}Existing report(s) from this template{/ts}</a></td>
                            {/if}
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="form-layout-compressed" align="right">                        
                        {if $chartSupported}
                            <tr>
                                <td>{$form.charts.html|crmReplace:class:big}</td>
                                <td align="right">{$form.$chart.html}</td>
                            </tr>
                        {/if}
                        {if $form.groups}
                            <tr>
                                <td>{$form.groups.html|crmReplace:class:big}</td>
                                <td align="right">{$form.$group.html}</td>
                            </tr>
                        {/if}
                    </table>
                </td>
            </tr>
        </table>
        </div>
    {/if}

    {literal}
    <script type="text/javascript">
    var flashChartType = {/literal}{if $chartType}'{$chartType}'{else}''{/if}{literal};
    function disablePrintPDFButtons( viewtype ) {
      if (viewtype && flashChartType != viewtype) {
        cj('#_qf_Summary_submit_pdf').attr('disabled', true).addClass('button-disabled');
	cj('#_qf_Summary_submit_print').attr('disabled', true).addClass('button-disabled');
      } else {
        cj('#_qf_Summary_submit_pdf').removeAttr('disabled').removeClass('button-disabled');
	cj('#_qf_Summary_submit_print').removeAttr('disabled').removeClass('button-disabled');
      }
    }
    </script>
    {/literal}

{*NYSS 5210/5214*}
<div id="pdfProcessing" style="display: none;">
    <p>Your PDF report is processing. Depending on the length of the report and number of records, this may take a few minutes to complete. You will be prompted to save the file once processing has finished.</p>
</div>
<div id="csvProcessing" style="display: none;">
    <p>Your CSV report is processing. Depending on the length of the report and number of records, this may take a few minutes to complete. You will be prompted to save the file once processing has finished.</p>
</div>

{literal}
<script type="text/javascript">
cj(".crm-tasks input[value='Create PDF']").click(function(){
    cj("#pdfProcessing").show( );
    cj("#pdfProcessing").dialog({
		title: "PDF Processing",
		modal: true,
		bgiframe: true,
		overlay: { opacity: 0.5, background: "black" },
		beforeclose: function(event, ui) { cj(this).dialog("destroy"); },
		buttons: { "Ok": function() { cj(this).dialog("close"); }}
	});
});
cj(".crm-tasks input[value='Export to CSV'], .crm-tasks input[value='Create CSV']").click(function(){
    cj("#csvProcessing").show( );
    cj("#csvProcessing").dialog({
		title: "CSV Processing",
		modal: true,
		bgiframe: true,
		overlay: { opacity: 0.5, background: "black" },
		beforeclose: function(event, ui) { cj(this).dialog("destroy"); },
		buttons: { "Ok": function() { cj(this).dialog("close"); }}
	});
});
</script>
{/literal}

{/if} {* NO print section ends *}
