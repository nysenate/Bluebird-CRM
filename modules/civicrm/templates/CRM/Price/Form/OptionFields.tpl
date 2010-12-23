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
<fieldset><legend>{ts}Price Field Options{/ts}</legend>
    <div class="description">
        {ts}Enter up to ten (10) multiple choice options in this table (click 'another choice' for each additional choice). If you need more than ten options, you can create an unlimited number of additional choices using the Edit Price Options link after saving this new field. Enter a description of the option in the 'Label' column, and the associated price in the 'Amount' column. Click the 'Default' radio button to the left of an option if you want that to be selected by default.{/ts}
    </div>
	{strip}
	<table id='optionField'>
	<tr>
        <th>&nbsp;</th>
	    <th>{ts}Default{/ts}</th>
        <th>{ts}Label{/ts}</th>
        <th>{ts}Amount{/ts} {help id="id-negative-options"}</th>
	    <th>{ts}Description{/ts}</th>
        {if $useForEvent}
	        <th>{ts}Participant Count{/ts}</th>
	        <th>{ts}Max Participant{/ts}</th>
	    {/if}
        <th>{ts}Weight{/ts}</th>
	    <th>{ts}Active?{/ts}</th>
    </tr>
	
	{section name=rowLoop start=1 loop=12}
	{assign var=index value=$smarty.section.rowLoop.index}
	<tr id="optionField_{$index}" class="form-item {cycle values="odd-row,even-row"}">
        <td> 
        {if $index GT 1}
            <a onclick="showHideRow({$index});" name="optionField_{$index}" href="javascript:void(0)" class="form-link"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}hide field or section{/ts}"/></a>
        {/if}
        </td>
	    <td> 
		<div id="radio{$index}" style="display:none">
		     {$form.default_option[$index].html} 
		</div>
		<div id="checkbox{$index}" style="display:none">
		     {$form.default_checkbox_option.$index.html} 
		</div>
	    </td>
	    <td> {$form.option_label.$index.html}</td>
	    <td> {$form.option_amount.$index.html|crmReplace:class:eight}</td>
	    <td> {$form.option_description.$index.html}</td>
        {if $useForEvent}
	      <td> {$form.option_count.$index.html}</td>
	      <td> {$form.option_max_value.$index.html}</td>
	    {/if}  
	    <td> {$form.option_weight.$index.html}</td>
 	    <td> {$form.option_status.$index.html}</td>
	</tr>
    {/section}
    </table>
	<div id="optionFieldLink" class="add-remove-link">
        <a onclick="showHideRow();" name="optionFieldLink" href="javascript:void(0)" class="form-link"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}show field or section{/ts}"/>{ts}another choice{/ts}</a>
    </div>
	<div id="additionalOption" class="description">
		{ts}If you need additional options - you can add them after you Save your current entries.{/ts}
	</div>
    {/strip}
    
</fieldset>
<script type="text/javascript">
    var showRows   = new Array({$showBlocks});
    var hideBlocks = new Array({$hideBlocks});
    var rowcounter = 0;
    {literal}
    if (navigator.appName == "Microsoft Internet Explorer") {    
	for ( var count = 0; count < hideBlocks.length; count++ ) {
	    var r = document.getElementById(hideBlocks[count]);
            r.style.display = 'none';
        }
    }
    {/literal}
    {* hide and display the appropriate blocks as directed by the php code *}
    on_load_init_blocks( showRows, hideBlocks, '' );
</script>
